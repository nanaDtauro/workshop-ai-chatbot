<?php

namespace App\Ai\Traits;

/**
 * Trait PromptTemplate
 * 
 * @author Ale Mostajo <https://github.com/amostajo>
 * @version 1.0.0
 */
trait PromptTemplate
{
    /**
     * Format a template string by replacing placeholders with actual values.
     * @since 1.0.0
     * 
     * @param string $template The template string containing placeholders in the format {key}.
     * @param array $data      An associative array where keys correspond to placeholders in the template and values are the replacements.
     * 
     * @return string The formatted template with placeholders replaced by their corresponding values.
     */
    protected function formatTemplate(string $template, array $data = []): string
    {
        foreach ($data as $key => $value) {
            $template = str_replace('{'.$key.'}', $value, $template);
        }
        return $template;
    }
}