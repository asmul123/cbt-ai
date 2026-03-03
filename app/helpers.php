<?php

/**
 * Clean and sanitize HTML content from rich text editor.
 *
 * @param string|null $html
 * @return string|null
 */
function clean_html(?string $html): ?string
{
    if (is_null($html) || trim($html) === '') {
        return null;
    }

    // Allow common HTML tags used in rich text editors
    $allowed = '<p><br><strong><b><em><i><u><s><strike><del><sub><sup>'
        . '<h1><h2><h3><h4><h5><h6>'
        . '<ul><ol><li>'
        . '<table><thead><tbody><tr><th><td>'
        . '<a><img><figure><figcaption>'
        . '<blockquote><pre><code><hr>'
        . '<span><div>';

    return strip_tags($html, $allowed);
}
