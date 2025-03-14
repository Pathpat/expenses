<?php

declare(strict_types=1);

namespace App;

use App\Contracts\SessionInterface;
use App\DataObjects\SessionConfig;
use App\Exception\SessionException;

class Session implements SessionInterface
{
    public function __construct(private readonly SessionConfig $options)
    {
    }

    /**
     * @return void
     */
    public function start(): void
    {
        if ($this->isActive()) {
            throw new SessionException('Session already started');
        }

        if (headers_sent($fileName, $line)) {
            throw new SessionException('Headers have already sent by'.$fileName.':'.$line);
        }

        session_set_cookie_params(
            [
                'secure' => $this->options->secure,
                'httponly' => $this->options->httpOnly,
                'samesite' => $this->options->sameSite->value,
            ]
        );

        if (!empty($this->options->name)) {
            session_name($this->options->name);
        }

        if (!session_start()) {
            throw new SessionException('Unable to start session');
        }
    }

    /**
     * @return void
     */
    public function save(): void
    {
        session_write_close();
    }

    public function isActive(): bool
    {
        return session_status() === PHP_SESSION_ACTIVE;
    }

    /**
     * @param  string  $key
     * @param  mixed|null  $default
     * @return mixed
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->has($key) ? $_SESSION[$key] : $default;
    }

    /**
     * @param  string  $key
     * @return bool
     */
    public function has(string $key): bool
    {
        return array_key_exists($key, $_SESSION);
    }

    /**
     * @return bool
     */
    public function regenerate(): bool
    {
        return session_regenerate_id();
    }

    /**
     * @param  string  $key
     * @param  mixed  $value
     * @return void
     */
    public function put(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    /**
     * @param  string  $key
     * @return void
     */
    public function forget(string $key): void
    {
        unset($_SESSION[$key]);
    }

    /**
     * @param  string  $key
     * @param  array  $messages
     * @return void
     */
    public function flash(string $key, array $messages): void
    {
        $_SESSION[$this->options->flashName][$key] = $messages;
    }

    /**
     * @param  string  $key
     * @return mixed
     */
    public function getFlash(string $key): array
    {
        $messages = $_SESSION[$this->options->flashName][$key] ?? [];

        unset($_SESSION[$this->options->flashName][$key]);

        return $messages;
    }
}