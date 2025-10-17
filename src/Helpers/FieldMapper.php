<?php

namespace Hexaora\CrudGenerator\Helpers;

use Illuminate\Support\Str;

class FieldMapper
{
    /**
     * Get appropriate Faker method for field type.
     */
    public static function getFakerMethodForField(array $field): string
    {
        $type = $field['type'];
        $name = $field['name'];
        
        // Check field name patterns first
        if (Str::contains($name, 'email')) {
            return '$this->faker->unique()->safeEmail()';
        }
        if (Str::contains($name, 'phone')) {
            return '$this->faker->phoneNumber()';
        }
        if (Str::contains($name, 'address')) {
            return '$this->faker->address()';
        }
        if (Str::contains($name, 'city')) {
            return '$this->faker->city()';
        }
        if (Str::contains($name, 'country')) {
            return '$this->faker->country()';
        }
        if (Str::contains($name, ['url', 'website', 'link'])) {
            return '$this->faker->url()';
        }
        if (Str::contains($name, 'image') || Str::contains($name, 'photo')) {
            return '$this->faker->imageUrl()';
        }
        if (Str::contains($name, 'color')) {
            return '$this->faker->hexColor()';
        }
        if (in_array($name, ['slug', 'handle'])) {
            return '$this->faker->slug()';
        }
        
        // Check by field type
        switch ($type) {
            case 'string':
                if (in_array('unique', $field['modifiers'] ?? [])) {
                    return '$this->faker->unique()->word()';
                }
                if (Str::contains($name, ['title', 'name', 'label'])) {
                    return '$this->faker->words(3, true)';
                }
                return '$this->faker->word()';
                
            case 'text':
            case 'longText':
            case 'mediumText':
                if (Str::contains($name, ['description', 'content', 'body', 'bio'])) {
                    return '$this->faker->paragraph()';
                }
                return '$this->faker->sentence()';
                
            case 'integer':
            case 'tinyInteger':
            case 'smallInteger':
            case 'mediumInteger':
            case 'bigInteger':
                return '$this->faker->numberBetween(1, 100)';
                
            case 'decimal':
            case 'float':
            case 'double':
                $precision = $field['modifiers'][1] ?? 2;
                return "\$this->faker->randomFloat({$precision}, 10, 1000)";
                
            case 'boolean':
                return '$this->faker->boolean()';
                
            case 'date':
                return '$this->faker->date()';
                
            case 'dateTime':
            case 'timestamp':
                return '$this->faker->dateTime()';
                
            case 'time':
                return '$this->faker->time()';
                
            case 'json':
                return '$this->faker->randomElements([\'key\' => \'value\'])';
                
            case 'uuid':
                return '$this->faker->uuid()';
                
            case 'ipAddress':
                return '$this->faker->ipv4()';
                
            case 'macAddress':
                return '$this->faker->macAddress()';
                
            case 'year':
                return '$this->faker->year()';
                
            case 'foreignId':
            case 'foreignIdFor':
                $relatedModel = $field['modifiers'][0] ?? null;
                if ($relatedModel) {
                    return "{$relatedModel}::factory()";
                }
                return '$this->faker->numberBetween(1, 10)';
                
            default:
                return '$this->faker->word()';
        }
    }

    /**
     * Generate factory fields from parsed fields.
     */
    public static function generateFactoryFields(array $fields): string
    {
        if (empty($fields)) {
            return "            'name' => \$this->faker->word(),\n            'description' => \$this->faker->sentence(),";
        }

        $fieldLines = [];
        foreach ($fields as $field) {
            $fakerMethod = self::getFakerMethodForField($field);
            $fieldLines[] = "            '{$field['name']}' => {$fakerMethod},";
        }

        return implode("\n", $fieldLines);
    }
}
