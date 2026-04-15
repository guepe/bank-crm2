<?php

namespace App\Service;

/**
 * Extract structured data from user responses
 */
class DataExtractionService
{
    /**
     * Extract contact data from conversation messages
     */
    public function extractContactData(array $messages): array
    {
        $data = [];

        foreach ($messages as $message) {
            if ($message['role'] !== 'user') {
                continue;
            }

            $content = $message['content'];

            // Simple pattern matching (will be improved with AI analysis)
            if (preg_match('/pr[eé]nom[:\s]+([a-zàâäéèêëïîôöœùûüçA-ZÀÂÄÉÈÊËÏÎÔÖŒÙÛÜÇ\s]+)/i', $content, $matches)) {
                $data['firstname'] = trim($matches[1]);
            }

            if (preg_match('/nom(?:\s+de\s+famille)?[:\s]+([a-zàâäéèêëïîôöœùûüçA-ZÀÂÄÉÈÊËÏÎÔÖŒÙÛÜÇ\s]+)/i', $content, $matches)) {
                $data['lastname'] = trim($matches[1]);
            }

            if (preg_match('/(?:date\s+de\s+)?naissance[:\s]*([0-9]{1,2}[\/-][0-9]{1,2}[\/-][0-9]{4})/i', $content, $matches)) {
                $data['birthdate'] = $this->parseDate($matches[1]);
            }

            if (preg_match('/(?:lieu\s+de\s+)?naissance[:\s]+([a-zàâäéèêëïîôöœùûüçA-ZÀÂÄÉÈÊËÏÎÔÖŒÙÛÜÇ\s]+)/i', $content, $matches)) {
                $data['birthplace'] = trim($matches[1]);
            }

            if (preg_match('/niss[:\s]*([0-9]{2}\s?[0-9]{2}\s?[0-9]{2}\s?[0-9]{3}|[0-9]{11})/i', $content, $matches)) {
                $data['niss'] = preg_replace('/\s+/', '', $matches[1]);
            }

            if (preg_match('/email[:\s]+([a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,})/i', $content, $matches)) {
                $data['email'] = trim($matches[1]);
            }

            if (preg_match('/t[eé]l[eé]phone[:\s]+([\+0-9\s\-().]+)/i', $content, $matches)) {
                $data['phone'] = $this->cleanPhoneNumber($matches[1]);
            }

            if (preg_match('/adresse[:\s]+([^\n]+)/i', $content, $matches)) {
                $data['address'] = trim($matches[1]);
            }

            if (preg_match('/profession[:\s]+([a-zàâäéèêëïîôöœùûüçA-ZÀÂÄÉÈÊËÏÎÔÖŒÙÛÜÇ\s]+)/i', $content, $matches)) {
                $data['profession'] = trim($matches[1]);
            }

            if (preg_match('/(?:état\s+civil|marital)[:\s]+([a-zàâäéèêëïîôöœùûüçA-ZÀÂÄÉÈÊËÏÎÔÖŒÙÛÜÇ]+)/i', $content, $matches)) {
                $data['maritalStatus'] = $this->parseMaritalStatus(trim($matches[1]));
            }

            if (preg_match('/(?:revenu|salaire)[:\s]*([0-9]+(?:[.,][0-9]{2})?)\s*(?:€|euros)?/i', $content, $matches)) {
                $data['incomeAmount'] = (float) str_replace(',', '.', $matches[1]);
            }
        }

        return array_filter($data);
    }

    /**
     * Extract account data from conversation
     */
    public function extractAccountData(array $messages): array
    {
        $data = [];

        foreach ($messages as $message) {
            if ($message['role'] !== 'user') {
                continue;
            }

            $content = $message['content'];

            if (preg_match('/(?:nom\s+)?(?:de\s+l[\'a])?(?:entreprise|soci[ée]t[ée]|raison\s+sociale)[:\s]+([^\n]+)/i', $content, $matches)) {
                $data['name'] = trim($matches[1]);
            }

            if (preg_match('/statut[:\s]+([^\n]+)/i', $content, $matches)) {
                $data['companyStatut'] = trim($matches[1]);
            }

            if (preg_match('/(?:type\s+de\s+)?compte[:\s]+(business|standard|core|potential)/i', $content, $matches)) {
                $data['type'] = trim($matches[1]);
            }
        }

        return array_filter($data);
    }

    /**
     * Parse various date formats
     */
    private function parseDate(string $date): ?string
    {
        $date = trim($date);
        $patterns = [
            '/(\d{1,2})[\/-](\d{1,2})[\/-](\d{4})/' => '$3-$2-$1',
            '/(\d{4})[\/-](\d{1,2})[\/-](\d{1,2})/' => '$1-$2-$3',
        ];

        foreach ($patterns as $pattern => $replacement) {
            if (preg_match($pattern, $date, $matches)) {
                $formatted = preg_replace($pattern, $replacement, $date);
                if (strtotime($formatted)) {
                    return $formatted;
                }
            }
        }

        return null;
    }

    /**
     * Clean phone number
     */
    private function cleanPhoneNumber(string $phone): string
    {
        return preg_replace('/[^\d+]/', '', $phone);
    }

    /**
     * Parse marital status text
     */
    private function parseMaritalStatus(string $status): ?int
    {
        $status = strtolower($status);
        $mapping = [
            'c[eé]libataire' => 1,
            'mari[eé]' => 2,
            'divorc[eé]' => 3,
            'veuf' => 4,
        ];

        foreach ($mapping as $pattern => $value) {
            if (preg_match("/$pattern/i", $status)) {
                return $value;
            }
        }

        return null;
    }
}
