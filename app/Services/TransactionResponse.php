<?php

namespace App\Services;

/**
 * Class TransactionResponse
 *
 * Represents an HTTP response.
 */
class TransactionResponse {
    public function __construct(
        protected int $status = 200,
        protected bool $success = false,
        protected ?string $message = null,
        protected ?array $data = null,
        protected ?string $link = null,
        protected ?string $uniqueId = null
    ) {

    }

    /**
     * Get a new instance of TransactionResponse
     *
     * @return static Returns the current instance of the object.
     */
    public static function new(): static {
        return new static();
    }

    /**
     * Set the response data.
     *
     * @param array $data The response data array.
     *
     * @return static Returns the current instance of the object.
     */
    public function data(array $data): static {
        $this->data = $data;
        return $this;
    }

    /**
     * Set the success flag.
     *
     * @param bool $success The success flag.
     *
     * @return static Returns the current instance of the object.
     */
    public function success(bool $success): static {
        $this->success = $success;
        return $this;
    }

    /**
     * Set the response message.
     *
     * @param string $message The response message.
     *
     * @return static Returns the current instance of the object.
     */
    public function message(string $message): static {
        $this->message = $message;
        return $this;
    }

    /**
     * Set the HTTP status code.
     *
     * @param int $status The HTTP status code.
     *
     * @return static Returns the current instance of the object.
     */
    public function status(int $status): static {
        $this->status = $status;
        return $this;
    }

    /**
     * Set the transaction unique ID.
     *
     * @param string $uniqueId The transaction unique ID.
     *
     * @return static Returns the current instance of the object.
     */
    public function uniqueId(string $uniqueId): static {
        $this->uniqueId = $uniqueId;
        return $this;
    }

    /**
     * Set the link.
     *
     * @param string $link The link.
     *
     * @return static Returns the current instance of the object.
     */
    public function link(string $link): static {
        $this->link = $link;
        return $this;
    }

    /**
     * Get the response status.
     *
     * @return int The HTTP status code.
     */
    public function getStatus(): int {
        return $this->status;
    }

    /**
     * Get the success flag.
     *
     * @return bool The success flag.
     */
    public function getSuccess(): bool {
        return $this->success;
    }

    /**
     * Get the response message.
     *
     * @return string|null The response message.
     */
    public function getMessage(): ?string {
        return $this->message;
    }

    /**
     * Get the response data.
     *
     * @return array|null The response data array.
     */
    public function getData(): ?array {
        return $this->data;
    }

    /**
     * Get the link.
     *
     * @return string|null The link.
     */
    public function getLink(): ?string {
        return $this->link;
    }

    /**
     * Get the unique transaction ID.
     *
     * @return string|null The unique transaction ID.
     */
    public function getUniqueId(): ?string {
        return $this->uniqueId;
    }

    /**
     * Create a successful response.
     *
     * @param int $status The HTTP status code.
     * @param string $message The response message.
     * @param mixed $data The response data.
     *
     * @return static Returns a new instance of the object.
     */
    public static function successful(int $status, string $message, mixed $data = []): static {
        return new static($status, true, $message, $data);
    }

    /**
     * Create a failure response.
     *
     * @param int $status The HTTP status code.
     * @param string $message The response message.
     * @param mixed $data The response data.
     *
     * @return static Returns a new instance of the object.
     */
    public static function failure(int $status, string $message, mixed $data = []): static {
        return new static($status, false, $message, $data);
    }
}
