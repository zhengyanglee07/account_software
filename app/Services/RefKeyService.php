<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;

class RefKeyService
{
    private function generateRandInt(): int
    {
        try {
            $randomId = random_int(100000000001, 999999999999);
        } catch (\Exception $e) {
            \Log::error('Random refkey failed to generate: ' . $e);
            throw $e;
        }

        return $randomId;
    }

    /**
     * Get value to be stored in model's reference_key.
     *
     * Note: reference_key is used to show in url in place of id
     *
     * Addition Aug 2020:
     * - refKeyColumn is changeable by using second param, default to 'reference_key'
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param string $refKeyColumn
     * @return int
     * @throws \Exception
     */
    public function getRefKey(Model $model, string $refKeyColumn = 'reference_key'): int
    {
        do {
            $key = $this->generateRandInt();
        } while ($model->where($refKeyColumn, $key)->exists());

        return $key;
    }
}
