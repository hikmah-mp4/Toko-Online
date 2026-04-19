<?php

namespace App\Helpers;

class ImageHelper
{
    public static function uploadAndResize($file, $directory, $fileName, $width = null, $height = null)
    {
        // Hilangkan slash di akhir agar tidak double-slash
        $directory = rtrim($directory, '/');

        // Path final
        $destinationPath = public_path($directory);

        // Pastikan folder tujuan ada
        if (!file_exists($destinationPath)) {
            mkdir($destinationPath, 0755, true);
        }

        $extension = strtolower($file->getClientOriginalExtension());
        $image = null;

        // Tentukan metode pembuatan gambar berdasarkan ekstensi file
        switch ($extension) {
            case 'jpeg':
            case 'jpg':
                $image = imagecreatefromjpeg($file->getRealPath());
                break;
            case 'png':
                $image = imagecreatefrompng($file->getRealPath());
                break;
            case 'gif':
                $image = imagecreatefromgif($file->getRealPath());
                break;
            default:
                throw new \Exception('Unsupported image type');
        }

        // Resize jika diperlukan
        if ($width) {
            $oldWidth = imagesx($image);
            $oldHeight = imagesy($image);
            $aspectRatio = $oldWidth / $oldHeight;

            if (!$height) {
                $height = $width / $aspectRatio; // maintain aspect ratio
            }

            $newImage = imagecreatetruecolor($width, $height);
            imagecopyresampled($newImage, $image, 0, 0, 0, 0, $width, $height, $oldWidth, $oldHeight);

            imagedestroy($image);
            $image = $newImage;
        }

        // Simpan gambar
        $savePath = $destinationPath . '/' . $fileName;

        switch ($extension) {
            case 'jpeg':
            case 'jpg':
                imagejpeg($image, $savePath);
                break;

            case 'png':
                imagepng($image, $savePath);
                break;

            case 'gif':
                imagegif($image, $savePath);
                break;
        }

        imagedestroy($image);

        return $fileName;
    }
}
