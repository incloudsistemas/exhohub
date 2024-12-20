<?php

namespace App\Services\Polymorphics;

use App\Services\BaseService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Illuminate\Support\Facades\Storage;

class MediaService extends BaseService
{
    public function __construct(protected Media $media)
    {
        //
    }

    public function mutateFormDataToCreate(Model $ownerRecord, array $data): array
    {
        $data['model_type'] = MorphMapByClass(model: $ownerRecord::class);
        $data['model_id'] = $ownerRecord->id;

        $data['collection_name'] = $data['collection_name'] ?? 'attachments';
        $data['disk'] = $data['disk'] ?? 'public';
        $data['conversions_disk'] = $data['conversions_disk'] ?? 'public';
        $data['manipulations'] = $data['manipulations'] ?? [];
        $data['custom_properties'] = $data['custom_properties'] ?? [];
        $data['generated_conversions'] = $data['generated_conversions'] ?? [];
        $data['responsive_images'] = $data['responsive_images'] ?? [];

        $processedData = [];
        foreach ($data['file_name'] as $fileName) {
            $fileData = $data;

            $fileData['file_name'] = $fileName;

            $fileData['mime_type'] = Storage::disk('public')
                ->mimeType($fileName);

            $fileData['size'] = Storage::disk('public')
                ->size($fileName);

            $processedData[] = $fileData;
        }

        return $processedData;
    }

    public function createAction(array $data, string $model, Model $ownerRecord): Model
    {
        return DB::transaction(function () use ($data, $model, $ownerRecord): Model {
            foreach ($data as $item) {
                $model::create($item);
            }

            return $ownerRecord;
        });
    }

    public function mutateFormDataToEdit(Media $media, array $data): array
    {
        if ($media->file_name !== $data['file_name']) {
            $data['mime_type'] = Storage::disk('public')
                ->mimeType($data['file_name']);

            $data['size'] = Storage::disk('public')
                ->size($data['file_name']);
        }

        return $data;
    }

    public function deleteAction(Media $media): bool
    {
        return DB::transaction(function () use ($media): bool {
            Storage::disk('public')
                ->delete($media->file_name);

            return $media->delete();
        });
    }
}
