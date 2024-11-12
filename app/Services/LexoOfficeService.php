<?php

namespace App\Services;

use DOMDocument;
use DOMXPath;
use Illuminate\Support\Facades\Http;

class LexoOfficeService
{
    protected $lexApiKey;
    protected $lexBaseUrl;
    public function __construct()
    {
        $this->lexApiKey = env('Lexo_Office_API'); 
        $this->lexBaseUrl = "https://api.lexoffice.io/v1/"; 
    }

    public function fetchContact($customer)
    {
        if (!$customer->lexo_uuid) {
            return $this->storeContact($customer);
        }
    
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->lexApiKey,
            'Accept' => 'application/json'
        ])->withoutVerifying()->get($this->lexBaseUrl . 'contacts/' . $customer->lexo_uuid);
    
        if ($response->status() === 404) {
            return $this->storeContact($customer);
        }
    
        if (!$response->successful()) {
            // Return the exact error message from the API
            return 'Error fetching contact: ' . $response->body();
        }
    
        return $response->json();
    }
    
    public function storeContact($customer)
    {
        // Prepare the salutation
        if (isset($customer->salutation) && $customer->salutation == 'Mr.') {
            $customerSalutation = __("Mr. salutation");
        } else if (isset($customer->salutation) && $customer->salutation == 'Ms.') {
            $customerSalutation = __("Ms. salutation");
        }

        // Initialize roles (with a non-empty array to be safe, even if it's an empty object)
        $roles = [
            'customer' => new \stdClass(),  // Empty object instead of an empty array
        ];

        // Initialize payload
        $payload = [
            'version' => 0,
            'roles' => $roles,
            'note' => $customer->notes ?? '',
            'archived' => false
        ];

        // Check if company name exists, send company data or just person data
        if ($customer->company_name) {
            // Send company data with contact person
            $payload['company'] = [
                'name' => $customer->company_name,
                'taxNumber' => $customer->tax_number ?? null,
                'vatRegistrationId' => null,
                'allowTaxFreeInvoices' => false,
                'contactPersons' => [
                    [
                        'salutation' => isset($customerSalutation) ? $customerSalutation : 'Herr/Frau',
                        'firstName' => $customer->first_name,
                        'lastName' => $customer->last_name,
                        'primary' => true,
                        'emailAddress' => $customer->email,
                        'phoneNumber' => $customer->phone ?? $customer->mobile_no ?? ''
                    ]
                ]
            ];
        } else {
            // Send only person data
            $payload['person'] = [
                'salutation' => $customer->salutation ?? 'Herr/Frau',
                'firstName' => $customer->first_name,
                'lastName' => $customer->last_name,
                'emailAddress' => $customer->email,
                'phoneNumber' => $customer->phone ?? $customer->mobile_no ?? ''
            ];
        }

        // Add address details
        $payload['addresses'] = [
            'billing' => [
                [
                    'street' => $customer->address_1 ?? '',
                    'zip' => $customer->zip_code ?? '',
                    'city' => $customer->city ?? '',
                    'countryCode' => 'DE'
                ]
            ]
        ];

        // Add email addresses and phone numbers
        $payload['emailAddresses'] = [
            'business' => [$customer->email ?? '']
        ];

        // Only add phone numbers if available
        if ($customer->phone || $customer->mobile_no) {
            $payload['phoneNumbers'] = [
                'business' => [$customer->phone ?? $customer->mobile_no]
            ];
        }

        // Ensure the payload is properly formatted

        // Send the API request
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->lexApiKey,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json'
        ])->withoutVerifying()->post($this->lexBaseUrl . 'contacts', $payload);


        if ($response->successful()) {
            $data = $response->json();
            $customer->update(['lexo_uuid' => $data['id']]);
            return $data;
        }

        // Return the exact error message from the API
        return 'Error storing contact: ' . $response->body();
    }

    public function storeInvoice($contact, $invoice)
    {
        // Initialize payload with basic invoice details
        $payload = [
            'version' => 0,
           'voucherDate' => $invoice->created_at->format('Y-m-d\T00:00:00.000P'),
            'voucherStatus'=>'draft',
            'title' => 'Rechnung',
            'introduction' => 'Ihre bestellten Positionen stellen wir Ihnen hiermit in Rechnung',
            'remark' => 'Vielen Dank für Ihren Einkauf',
            'archived' => false,
            'address' => [
                'contactId' => $contact['id'],
            ]
        ];

        // Prepare line items from invoice items
        $lineItems = [];

        foreach ($invoice->items as $item) {

            $html = $item->description;
            $dom = new DOMDocument();
            @$dom->loadHTML($html);
            $xpath = new DOMXPath($dom);

            // Extract values based on class names
            $name = $xpath->query("//span[@class='pname_value']")->item(0)->textContent ?? '';
            $quantity = $xpath->query("//span[@class='pquantity_value']")->item(0)->textContent ?? '';
            $price = $xpath->query("//span[@class='pprice_value']")->item(0)->textContent ?? '';
            $total = $xpath->query("//span[@class='ptotal_value']")->item(0)->textContent ?? '';
            $currentProgress = $xpath->query("//span[@class='progress_value']")->item(0)->textContent ?? '';
            $totalProgress = $xpath->query("//span[@class='ptotalprogress_value']")->item(0)->textContent ?? '';

            $formattedDescription = __("Name") . ": $name - " . 
                                    __("Quantity") . ": $quantity - " . 
                                    __("Price") . ": $price - " . 
                                    __("Total Price") . ": $total - " . 
                                    __("Current Progress") . ": $currentProgress - " . 
                                    __("Total Progress") . ": $totalProgress";

            // Add group as a text element
            if ($item->group->group_name) {
                $lineItems[] = [
                    "type" => "text",
                    "name" => $item->group->group_name,
                    "description" => "Group: " . $item->group->group_name,
                ];
            }

            // Add the actual item as a custom line item with its details
            $lineItems[] = [
                "type" => "custom",
                "name" => $item->item,
                "quantity" => $item->quantity,
                "unitName" => $item->unit ?? 'Stück',
                "unitPrice" => [
                    "currency" => "EUR",
                    "netAmount" => $item->price,
                    "taxRatePercentage" => 19,
                ],
                "discountPercentage" => $invoice->discount,
                "description" =>  $formattedDescription, 
            ];
        }

        $payload['lineItems'] = $lineItems;

        $payload['totalPrice'] = [
            "currency" => "EUR"
        ];

        // Tax conditions and amounts
        $payload['taxAmounts'] = [
            [
                'taxRatePercentage' => 19,
            ]
        ];

        $payload['taxConditions'] = [
            'taxType' => 'net' // Change to 'gross' if needed
        ];

        // Shipping conditions
        $payload['shippingConditions'] = [
            'shippingDate' => now()->addDays(3)->format('Y-m-d\T00:00:00.000P'),
            'shippingType' => 'delivery'
        ];
       
        // Send the request to LexOffice
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->lexApiKey,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json'
        ])->withoutVerifying()->post($this->lexBaseUrl . 'invoices', $payload);
       
        // Check response
        if ($response->successful()) {
            return $response->json();
            $invoice->update(['is_sent_lexo' => 1]);
        } else {
            return 'Error storing invoice: ' . $response->body();
        }
    }



}
