<?php

namespace App\Services;

use App\Models\Lead;
use Exception;

class LeadService
{
    /**
     * Capture or update a lead based on user action.
     *
     * @param array $data
     * @return Lead
     * @throws Exception
     */
    public function captureLead(array $data): Lead
    {
        $buyerId = $data['buyer_id'];
        $sellerId = $data['seller_id'];
        
        if ($buyerId == $sellerId) {
            throw new Exception("You cannot generate a lead for your own item.");
        }

        $source = $data['source'] ?? Lead::SOURCE_VIEW_NUMBER;
        $temperature = $this->calculateTemperature($source);

        // Find existing lead
        $lead = Lead::where('buyer_id', $buyerId)
            ->where('seller_id', $sellerId)
            ->where(function ($query) use ($data) {
                if (!empty($data['product_id'])) {
                    $query->where('product_id', $data['product_id']);
                } elseif (!empty($data['requirement_id'])) {
                    $query->where('requirement_id', $data['requirement_id']);
                }
            })
            ->first();

        if ($lead) {
            // Update existing lead if new action is hotter or if we have a new message/quantity
            $updates = ['updated_at' => now()];

            if ($temperature > $lead->temperature) {
                $updates['temperature'] = $temperature;
                $updates['source'] = $source; // Update source to the hotter one
            }

            if (isset($data['quantity']) && !empty($data['quantity'])) {
                $updates['quantity'] = $data['quantity'];
            }
            if (isset($data['message']) && !empty($data['message'])) {
                $updates['message'] = $data['message'];
            }

            $lead->update($updates);
        } else {
            // Create new lead
            $lead = Lead::create([
                'buyer_id' => $buyerId,
                'seller_id' => $sellerId,
                'product_id' => $data['product_id'] ?? null,
                'requirement_id' => $data['requirement_id'] ?? null,
                'source' => $source,
                'temperature' => $temperature,
                'quantity' => $data['quantity'] ?? null,
                'message' => $data['message'] ?? null,
                'status' => Lead::STATUS_NEW,
            ]);

            // Increment leads_count on the Product if applicable
            if (!empty($data['product_id'])) {
                \App\Models\Product::where('id', $data['product_id'])->increment('leads_count');
            }
        }

        // AUTO CONVERT INQUIRY TO CHAT
        if (!empty($data['message']) && in_array($source, [Lead::SOURCE_INQUIRY_FORM, Lead::SOURCE_VIEW_NUMBER])) {
            try {
                $conversation = \App\Models\Conversation::firstOrCreate(
                    [
                        'product_id' => $data['product_id'] ?? null,
                        'requirement_id' => $data['requirement_id'] ?? null,
                        'buyer_id' => $buyerId,
                        'seller_id' => $sellerId,
                    ],
                    [
                        'status' => \App\Models\Conversation::STATUS_ACTIVE,
                    ]
                );

                $msgBody = $data['message'];
                if (!empty($data['quantity'])) {
                    $msgBody = "Required Quantity: " . $data['quantity'] . "\n\n" . $msgBody;
                }

                // Create the message if it doesn't exist to avoid duplicates if captureLead is called repeatedly
                $messageExists = $conversation->messages()
                    ->where('sender_id', $buyerId)
                    ->where('body', $msgBody)
                    ->where('created_at', '>=', now()->subMinutes(5))
                    ->exists();

                if (!$messageExists) {
                    $message = $conversation->messages()->create([
                        'sender_id' => $buyerId,
                        'type' => \App\Models\Message::TYPE_TEXT,
                        'body' => $msgBody,
                    ]);

                    $conversation->update(['last_message_at' => now()]);
                    $conversation->incrementUnreadForOther($buyerId);

                    // Broadcast Event via Pusher
                    broadcast(new \App\Events\MessageSent($message, $sellerId))->toOthers();
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("Failed to auto-convert inquiry to chat: " . $e->getMessage());
            }
        }

        return $lead;
    }

    /**
     * Calculate temperature based on source.
     */
    private function calculateTemperature(int $source): int
    {
        return match ($source) {
            Lead::SOURCE_INQUIRY_FORM => Lead::TEMP_HOT,
            Lead::SOURCE_CALL => Lead::TEMP_HOT,
            Lead::SOURCE_CHAT => Lead::TEMP_WARM,
            Lead::SOURCE_VIEW_NUMBER => Lead::TEMP_COLD,
            default => Lead::TEMP_COLD,
        };
    }
}
