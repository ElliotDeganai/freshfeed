<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

class ImageUploadService
{
    /**
     * Stocke une image en la redimensionnant/compressant automatiquement si elle
     * dépasse les limites voulues, plutôt que de rejeter l'upload. La validation
     * Laravel en amont ne garde qu'un plafond absolu généreux (voir contrôleurs) —
     * c'est ce service qui ramène l'image à une taille raisonnable pour le web.
     *
     * @param  UploadedFile  $file
     * @param  string  $directory       dossier de destination sur le disque (ex: 'posts')
     * @param  int  $maxWidth           largeur max en pixels — l'image n'est jamais agrandie
     * @param  int  $maxHeight          hauteur max en pixels
     * @param  int  $targetMaxBytes     poids cible après compression (octets)
     * @param  string  $disk            disque Laravel (par défaut 'public')
     * @return string  chemin stocké, à utiliser comme les autres `->store()`
     */
    public function store(
        UploadedFile $file,
        string $directory,
        int $maxWidth = 1600,
        int $maxHeight = 1600,
        int $targetMaxBytes = 2 * 1024 * 1024,
        string $disk = 'public',
    ): string {
        $extension = strtolower($file->getClientOriginalExtension() ?: 'jpg');

        // SVG : format vectoriel, pas de "redimensionnement" au sens photo — les
        // fichiers SVG (logos, icônes) sont de toute façon légers. On stocke tel quel.
        if ($extension === 'svg' || $file->getMimeType() === 'image/svg+xml') {
            return $file->store($directory, $disk);
        }

        $image = Image::read($file->getRealPath());

        // Ne redimensionne QUE si l'image dépasse le max — scaleDown() n'agrandit jamais.
        if ($image->width() > $maxWidth || $image->height() > $maxHeight) {
            $image->scaleDown($maxWidth, $maxHeight);
        }

        // GIF conservé tel quel après redimensionnement (l'encodage par qualité ne
        // s'applique pas de la même façon aux animations) ; jpg/png/webp compressés
        // progressivement jusqu'à repasser sous le poids cible.
        if ($extension === 'gif') {
            $encoded = (string) $image->encodeByExtension($extension);
        } else {
            $quality = 85;
            $encoded = (string) $image->encodeByExtension($extension, quality: $quality);

            while (strlen($encoded) > $targetMaxBytes && $quality > 35) {
                $quality -= 15;
                $encoded = (string) $image->encodeByExtension($extension, quality: $quality);
            }
        }

        $filename = trim($directory, '/') . '/' . uniqid('img_') . '.' . $extension;
        Storage::disk($disk)->put($filename, $encoded);

        return $filename;
    }
}
