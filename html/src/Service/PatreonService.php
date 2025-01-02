<?php
namespace App\Service;

use Patreon\API;

class PatreonService
{
    public function fetchPatreonData($accessToken)
    {
        $api_client = new API($accessToken);
        $response = $api_client->fetch_user();

        $result = [
            'membership_status' => null,
            'membership_type' => null,
            'currently_entitled_amount_cents' => null,
            'last_charge_date' => null,
            'lifetime_support_cents' => null,
            'pledge_relationship_start' => null,
        ];
    
        // Check if the 'included' key exists and contains data
        if (isset($response['included']) && is_array($response['included'])) {
            foreach ($response['included'] as $included) {
                if (isset($included['attributes'])) {
                    $attributes = $included['attributes'];
    
                    // Extract information if available
                    $result['membership_status'] = $attributes['patron_status'] ?? null;
                    $result['membership_type'] = $included['type'] ?? null;
                    $result['currently_entitled_amount_cents'] = $attributes['currently_entitled_amount_cents'] ?? null;
                    $result['last_charge_date'] = $attributes['last_charge_date'] ?? null;
                    $result['lifetime_support_cents'] = $attributes['lifetime_support_cents'] ?? null;
                    $result['pledge_relationship_start'] = $attributes['pledge_relationship_start'] ?? null;
                }
            }
        }
    
        return $result;

    }

    public function isActivePatreonMember($accessToken): bool
    {
        $patronResponse = $this->fetchPatreonData($accessToken);

        // Ensure 'included' key exists in the response
        if (isset($patronResponse['included']) && is_array($patronResponse['included'])) {
            foreach ($patronResponse['included'] as $included) {
                // Check if 'attributes' and 'patron_status' are present
                if (isset($included['attributes']['patron_status']) && $included['attributes']['patron_status'] === 'active_patron') {
                    
                    return true;
                }
            }
        }
        return false;
    }

    
    public function updatePatreonStatus($user)
    {
    }
}