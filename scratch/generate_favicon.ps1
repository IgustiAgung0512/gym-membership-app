Add-Type -AssemblyName System.Drawing

$size = 64
$bmp = [System.Drawing.Bitmap]::new($size, $size)
$g = [System.Drawing.Graphics]::FromImage($bmp)
$g.SmoothingMode = [System.Drawing.Drawing2D.SmoothingMode]::AntiAlias
$g.TextRenderingHint = [System.Drawing.Text.TextRenderingHint]::AntiAliasGridFit

# Bright Lime Green gradient background
$rect = [System.Drawing.Rectangle]::new(0, 0, $size, $size)
$c1 = [System.Drawing.ColorTranslator]::FromHtml('#BEF264')
$c2 = [System.Drawing.ColorTranslator]::FromHtml('#84CC16')
$brushBg = [System.Drawing.Drawing2D.LinearGradientBrush]::new($rect, $c1, $c2, 45)

# Rounded rectangle
$path = [System.Drawing.Drawing2D.GraphicsPath]::new()
$radius = 14
$diameter = $radius * 2
$arcRect = [System.Drawing.Rectangle]::new(0, 0, $diameter, $diameter)
$path.AddArc($arcRect, 180, 90)
$arcRect.X = $size - $diameter
$path.AddArc($arcRect, 270, 90)
$arcRect.Y = $size - $diameter
$path.AddArc($arcRect, 0, 90)
$arcRect.X = 0
$path.AddArc($arcRect, 90, 90)
$path.CloseFigure()

$g.FillPath($brushBg, $path)

# Draw bold black/dark slate 'G'
$font = [System.Drawing.Font]::new('Arial Black', [float]34, [System.Drawing.FontStyle]::Bold, [System.Drawing.GraphicsUnit]::Pixel)
$brushText = [System.Drawing.SolidBrush]::new([System.Drawing.ColorTranslator]::FromHtml('#0F172A'))
$format = [System.Drawing.StringFormat]::new()
$format.Alignment = [System.Drawing.StringAlignment]::Center
$format.LineAlignment = [System.Drawing.StringAlignment]::Center
$textRect = [System.Drawing.RectangleF]::new(0, 2, $size, $size)
$g.DrawString('G', $font, $brushText, $textRect, $format)

# Draw small accent dot
$brushDot = [System.Drawing.SolidBrush]::new([System.Drawing.ColorTranslator]::FromHtml('#0F172A'))
$g.FillEllipse($brushDot, 48, 10, 7, 7)

# Save high-res PNG
$bmp.Save('public/favicon.png', [System.Drawing.Imaging.ImageFormat]::Png)

# Convert to standard icon
$hIcon = $bmp.GetHicon()
$icon = [System.Drawing.Icon]::FromHandle($hIcon)
if (Test-Path 'public/favicon.ico') { Remove-Item 'public/favicon.ico' -Force }
$fs = [System.IO.File]::OpenWrite('public/favicon.ico')
$icon.Save($fs)
$fs.Close()

$g.Dispose()
$bmp.Dispose()
Write-Output 'Bright high-contrast favicon generated successfully'
