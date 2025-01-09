<?php

namespace App;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\CsvData
 *
 * @property int $id
 * @property string $csv_filename
 * @property int $csv_header
 * @property string $csv_data
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|CsvData newModelQuery()
 * @method static Builder|CsvData newQuery()
 * @method static Builder|CsvData query()
 * @method static Builder|CsvData whereCreatedAt($value)
 * @method static Builder|CsvData whereCsvData($value)
 * @method static Builder|CsvData whereCsvFilename($value)
 * @method static Builder|CsvData whereCsvHeader($value)
 * @method static Builder|CsvData whereId($value)
 * @method static Builder|CsvData whereUpdatedAt($value)
 * @mixin Eloquent
 */
class CsvData extends Model
{
    protected $table = "csv_data";
    protected $guarded = [];
}
