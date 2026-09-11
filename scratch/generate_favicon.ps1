Add-Type -AssemblyName System.Drawing

$size = 64
$bmp = [System.Drawing.Bitmap]::new($size, $size)
$g = [System.Drawing.Graphics]::FromImage($bmp)
$g.SmoothingMode = [System.Drawing.Drawing2D.SmoothingMode]::AntiAlias
$g.TextRenderingHint = [System.Drawing.Text.TextRenderingHint]::AntiAliasGridFit

# Background dark slate
$brushBg = [System.Drawing.SolidBrush]::new([System.Drawing.ColorTranslator]::FromHtml('#0F172A'))

# Rounded rectangle
$path = [System.Drawing.Drawing2D.GraphicsPath]::new()
$radius = 16
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

# Draw 'G'
$font = [System.Drawing.Font]::new('Arial', [float]32, [System.Drawing.FontStyle]::Bold, [System.Drawing.GraphicsUnit]::Pixel)
$brushText = [System.Drawing.SolidBrush]::new([System.Drawing.ColorTranslator]::FromHtml('#84CC16'))
$format = [System.Drawing.StringFormat]::new()
$format.Alignment = [System.Drawing.StringAlignment]::Center
$format.LineAlignment = [System.Drawing.StringAlignment]::Center
$textRect = [System.Drawing.RectangleF]::new(0, 4, $size, $size)
$g.DrawString('G', $font, $brushText, $textRect, $format)

# Draw small dot
$brushDot = [System.Drawing.SolidBrush]::new([System.Drawing.ColorTranslator]::FromHtml('#A3E635'))
$g.FillEllipse($brushDot, 46, 12, 8, 8)

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
Write-Output 'Favicon generated successfully'
