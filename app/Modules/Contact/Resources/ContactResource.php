<?php

namespace App\Modules\Contact\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Override;

class ContactResource extends JsonResource
{
    #[Override]
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'name'       => $this->name,
            'email'      => $this->email,
            'phone'      => $this->phone,
            'company'    => $this->company,
            'subject'    => $this->subject,
            'message'    => $this->message,
            'created_at' => $this->created_at->format('d-m-Y H:i:s'),
        ];
    }
}
