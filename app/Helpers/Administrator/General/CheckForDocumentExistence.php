<?php

namespace App\Helpers\Administrator\General;

class CheckForDocumentExistence
{
    public static function exists($model, array $conditions, bool $isUpdate = false, $excludeValue = null, string $excludeColumn = 'id', $returnMessage)
    {
        $isAlreadyExist = $model::where($conditions)
            ->when($isUpdate, function ($query) use ($excludeColumn, $excludeValue) {
                return $query->where($excludeColumn, '!=', $excludeValue);
            })->exists();

        if ($isAlreadyExist) {
            return response()->json(['success' => false, 'message' => $returnMessage], 409);
        }

        return null;
    }
}
