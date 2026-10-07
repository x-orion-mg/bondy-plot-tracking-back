<?php

namespace App\Utils;

use App\Utils\PathName;

class Utils
{
    /**
     * Normalize a string to use it as a slug
     *
     * The following transformations are applied:
     * - Lowercase the string
     * - Replace non-alphanumeric characters with dashes
     * - Remove leading and trailing dashes
     *
     * @param string $text The string to normalize
     * @return string The normalized string
     */
    function slugify(string $text): string
    {
        $text = strtolower($text);
        $text = preg_replace('/[^a-z0-9]+/i', '-', $text);
        $text = trim($text, '-');
        return $text;
    }

    /**
     * Format a date as a string, in French
     *
     * @param \DateTimeInterface $date The date to format
     * @return string The formatted date string, in the format "Month Year"
     */
    public function formatDate($date)
    {
        setlocale(LC_TIME, 'fr_FR.UTF-8', 'fr_FR', 'fr');
        $formatted_date = strftime('%B %Y', $date->getTimestamp());
        $formatted_date = mb_convert_encoding($formatted_date, 'UTF-8', mb_detect_encoding($formatted_date, 'ISO-8859-1, UTF-8', true));
        $formatted_date = preg_replace('/\s+/', ' ', $formatted_date);

        return ucfirst($formatted_date);
    }

    /**
     * Generate slug
     * @param string $string
     * @param string $separator
     * @return string
     */
    public static function slug($string, $separator = '-')
    {
        $accents_regex = '~&([a-z]{1,2})(?:acute|cedil|circ|grave|lig|orn|ring|slash|th|tilde|uml);~i';
        $special_cases = array('&' => 'and', "'" => '');

        $string = mb_strtolower(trim($string), 'UTF-8');
        $string = str_replace(array_keys($special_cases), array_values($special_cases), $string);
        $string = preg_replace($accents_regex, '$1', htmlentities($string, ENT_QUOTES, 'UTF-8'));
        $string = preg_replace("/[^a-z0-9]/u", "$separator", $string);
        $string = preg_replace("/[$separator]+/u", "$separator", $string);

        return $string;
    }

    /**
     * Generate a secure token
     *
     * This function generates a secure token using a combination of a random salt and the current timestamp.
     * The token is hashed using the SHA-256 algorithm for enhanced security.
     *
     * @return string The generated secure token
     */

    public function generateToken()
    {
        $salt = bin2hex(random_bytes(32));
        $timestamp = time();
        $token = hash('sha256', $salt . $timestamp);

        return $token;
    }

    public function generateTrackCode($parcel): string
    {
        $startDate = $parcel->getStartDate() ? '-' . $parcel->getStartDate()->format('Y') : '-' . date('Y');
        $parcelId = $parcel->getId() ? '-' . $parcel->getId() : '';

        return 'BON' . $startDate . $parcelId . '-';
    }

    public function getTrackCodeUrl(string $trackCode): string
    {
        $date = new \DateTime();
        preg_match('/BON-\d{4}-\d-\d{5}/', $trackCode, $matches);
        return $matches[0] ?? 'BON-' . $date->format('Y') . '-';
    }

    public function isAdmin($user): bool
    {
        if (!$user || !$user->getProfile()) {
            return false;
        }

        return in_array($user->getProfile()->getCode(), [
            PathName::PROFILE_ADMIN,
            PathName::PROFILE_SUPER_ADMIN
        ]);
    }

    public function convertCoordinates(string $coordinate): float
    {
        // Remplace les virgules par des points et convertit en float
        return (float)str_replace(',', '.', $coordinate);
    }
}
