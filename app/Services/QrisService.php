<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class QrisService
{
    /**
     * Generate Dynamic QRIS Data for an Order with Cryptographic Integrity Protection
     */
    public static function generate(Order $order): array
    {
        return self::generateGeneric(
            invoiceNumber: $order->invoice_number,
            amount: (int) $order->total_amount,
            id: $order->id,
            merchantName: 'GYMPULSE STORE',
            orderObject: $order
        );
    }

    /**
     * Generate Dynamic QRIS Data for a Membership Renewal Payment
     */
    public static function generateForRenewal(\App\Models\Payment $payment): array
    {
        $invoice = $payment->invoice_number ?: ('RNW-' . now()->format('ymd') . '-' . str_pad($payment->id, 4, '0', STR_PAD_LEFT));
        return self::generateGeneric(
            invoiceNumber: $invoice,
            amount: (int) $payment->amount,
            id: $payment->id,
            merchantName: 'GYMPULSE MEMBERSHIP'
        );
    }

    /**
     * Internal Generic Dynamic QRIS Generator with Cryptographic Integrity & Midtrans Bridge
     */
    public static function generateGeneric(string $invoiceNumber, int $amount, $id = null, string $merchantName = 'GYMPULSE', ?Order $orderObject = null): array
    {
        $serverKey = config('services.midtrans.server_key', env('MIDTRANS_SERVER_KEY'));
        $isProduction = config('services.midtrans.is_production', env('MIDTRANS_IS_PRODUCTION', false));
        $expiresAt = now()->addMinutes(15)->toISOString();

        // 1. If Midtrans Key is configured, attempt Midtrans Official API
        if (!empty($serverKey)) {
            try {
                $endpoint = $isProduction 
                    ? 'https://api.midtrans.com/v2/charge' 
                    : 'https://api.sandbox.midtrans.com/v2/charge';

                $response = Http::withBasicAuth($serverKey, '')
                    ->timeout(10)
                    ->post($endpoint, [
                        'payment_type' => 'qris',
                        'transaction_details' => [
                            'order_id' => $invoiceNumber,
                            'gross_amount' => $amount,
                        ],
                        'qris' => [
                            'acquirer' => 'gopay',
                        ],
                    ]);

                if ($response->successful()) {
                    $data = $response->json();
                    $qrString = $data['qr_string'] ?? null;
                    $qrUrl = $data['actions'][0]['url'] ?? null;
                    $signature = $orderObject 
                        ? self::generateSignature($orderObject, $expiresAt)
                        : self::generateGenericSignature($invoiceNumber, $amount, $id, $expiresAt);

                    return [
                        'mode' => 'midtrans',
                        'qr_string' => $qrString,
                        'qr_url' => $qrUrl ?: "https://api.qrserver.com/v1/create-qr-code/?size=350x350&data=" . urlencode($qrString),
                        'signature' => $signature,
                        'expires_at' => $expiresAt,
                        'merchant_name' => $merchantName,
                        'amount' => $amount,
                        'invoice_number' => $invoiceNumber,
                    ];
                } else {
                    Log::warning('Midtrans QRIS creation returned error, falling back to simulator', [
                        'response' => $response->body()
                    ]);
                }
            } catch (\Throwable $e) {
                Log::error('Midtrans QRIS connection error: ' . $e->getMessage());
            }
        }

        // 2. High-Fidelity Built-in QRIS Generator (Bank Indonesia / ASPI & EMVCo Standard)
        $city = 'JAKARTA';
        $invoice = $invoiceNumber;

        // Construct standard EMVCo dynamic QRIS structure (Tag-Length-Value)
        $payloadWithoutCrc = "000201" // Format Indicator: 01
            . "010212" // Initiation Method: 12 (Dynamic)
            . "26680014ID.CO.QRIS.WWW01189360099900000000000215" . $invoice . "0303UME" // Merchant Tag 26
            . "51440014ID.CO.QRIS.WWW0215ID1020000000000" // Merchant Tag 51
            . "52045999" // Merchant Category: 5999
            . "5303360"  // Currency: 360 (IDR)
            . "54" . str_pad((string)strlen((string)$amount), 2, '0', STR_PAD_LEFT) . $amount // Transaction Amount
            . "5802ID"   // Country Code: ID
            . "59" . str_pad((string)strlen($merchantName), 2, '0', STR_PAD_LEFT) . $merchantName // Merchant Name
            . "60" . str_pad((string)strlen($city), 2, '0', STR_PAD_LEFT) . $city // City
            . "62" . str_pad((string)(4 + strlen($invoice)), 2, '0', STR_PAD_LEFT) . "01" . str_pad((string)strlen($invoice), 2, '0', STR_PAD_LEFT) . $invoice // Additional Data (Invoice)
            . "6304";    // CRC16 Tag

        // Compute EMVCo standard CRC16-CCITT checksum
        $crc16 = self::calculateCrc16($payloadWithoutCrc);
        $fullQrisPayload = $payloadWithoutCrc . $crc16;

        $qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=350x350&margin=10&data=" . urlencode($fullQrisPayload);
        $signature = $orderObject 
            ? self::generateSignature($orderObject, $expiresAt)
            : self::generateGenericSignature($invoiceNumber, $amount, $id, $expiresAt);

        return [
            'mode' => 'simulator',
            'qr_string' => $fullQrisPayload,
            'qr_url' => $qrUrl,
            'signature' => $signature,
            'merchant_name' => $merchantName,
            'amount' => $amount,
            'invoice_number' => $invoice,
            'expires_at' => $expiresAt,
            'crc16' => $crc16,
        ];
    }

    /**
     * Generate Cryptographic Signature for Generic Transaction
     */
    public static function generateGenericSignature(string $invoiceNumber, int $amount, $id, string $expiresAt): string
    {
        $appKey = config('app.key') ?: 'gympulse_secret_signing_key_2026';
        $payload = implode('|', [
            $invoiceNumber,
            $amount,
            $id,
            $expiresAt,
        ]);

        return hash_hmac('sha256', $payload, $appKey);
    }

    /**
     * Calculate Standard EMVCo CRC-16-CCITT (0x1021, Init 0xFFFF)
     */
    public static function calculateCrc16(string $str): string
    {
        $crc = 0xFFFF;
        $len = strlen($str);
        for ($c = 0; $c < $len; $c++) {
            $crc ^= (ord($str[$c]) << 8);
            for ($i = 0; $i < 8; $i++) {
                if ($crc & 0x8000) {
                    $crc = (($crc << 1) ^ 0x1021) & 0xFFFF;
                } else {
                    $crc = ($crc << 1) & 0xFFFF;
                }
            }
        }
        return strtoupper(str_pad(dechex($crc), 4, '0', STR_PAD_LEFT));
    }

    /**
     * Verify QRIS Payload Checksum Integrity (Anti-Tampering)
     */
    public static function verifyPayloadChecksum(string $qrisPayload): bool
    {
        if (strlen($qrisPayload) < 8) {
            return false;
        }

        $payloadWithoutCrc = substr($qrisPayload, 0, -4);
        $providedCrc = strtoupper(substr($qrisPayload, -4));
        $calculatedCrc = self::calculateCrc16($payloadWithoutCrc);

        return hash_equals($calculatedCrc, $providedCrc);
    }

    /**
     * Generate Cryptographic Transaction Signature (HMAC-SHA256)
     */
    public static function generateSignature(Order $order, string $expiresAt): string
    {
        $appKey = config('app.key') ?: 'gympulse_secret_signing_key_2026';
        $payload = implode('|', [
            $order->invoice_number,
            (int) $order->total_amount,
            $order->id,
            $expiresAt,
        ]);

        return hash_hmac('sha256', $payload, $appKey);
    }

    /**
     * Verify Cryptographic Transaction Signature (Anti-Spoofing)
     */
    public static function verifySignature(Order $order, string $signature, string $expiresAt): bool
    {
        $expectedSignature = self::generateSignature($order, $expiresAt);
        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Compute Midtrans Notification Expected Signature (SHA-512)
     */
    public static function computeMidtransSignature(string $orderId, string $statusCode, string $grossAmount, string $serverKey): string
    {
        return hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);
    }

    /**
     * Verify Midtrans Signature with Timing-Attack Safe Comparison
     */
    public static function verifyMidtransNotification(string $orderId, string $statusCode, string $grossAmount, string $receivedSignature, ?string $serverKey = null): bool
    {
        $serverKey = $serverKey ?: config('services.midtrans.server_key', env('MIDTRANS_SERVER_KEY'));
        if (empty($serverKey)) {
            return false;
        }

        $expectedSignature = self::computeMidtransSignature($orderId, $statusCode, $grossAmount, $serverKey);
        return hash_equals($expectedSignature, $receivedSignature);
    }
}
