<?php

namespace App\Http\Resources;

class NotificationResponse
{
    public bool $success;
    public ?string $to;
    public ?string $sid;
    public ?string $status;
    public ?string $error;

    public function __construct(
        bool $success,
        ?string $to = null,
        ?string $sid = null,
        ?string $status = null,
        ?string $error = null
    ) {
        $this->success = $success;
        $this->to      = $to;
        $this->sid     = $sid;
        $this->status  = $status;
        $this->error   = $error;
    }

    /**
     * Conversion en tableau (utile pour API JSON)
     */
    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'to'      => $this->to,
            'sid'     => $this->sid,
            'status'  => $this->status,
            'error'   => $this->error,
        ];
    }
}
