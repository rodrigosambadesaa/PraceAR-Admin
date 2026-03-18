<?php

declare(strict_types=1);

namespace App\Http;

final class Request
{
    /** @var array<string, mixed> */
    private array $query;

    /** @var array<string, mixed> */
    private array $request;

    /** @var array<string, mixed> */
    private array $server;

    /** @param array<string, mixed> $query
     *  @param array<string, mixed> $request
     *  @param array<string, mixed> $server
     */
    public function __construct(array $query, array $request, array $server)
    {
        $this->query = $query;
        $this->request = $request;
        $this->server = $server;
    }

    public static function fromGlobals(): self
    {
        return new self($_GET, $_REQUEST, $_SERVER);
    }

    public function page(): string
    {
        $page = $this->request['page'] ?? 'index';
        return is_string($page) && $page !== '' ? $page : 'index';
    }

    public function currentPageNumber(): int
    {
        $pageNumber = $this->query['page_number'] ?? 1;
        if (!is_numeric($pageNumber)) {
            return 1;
        }

        $parsed = (int) $pageNumber;
        return $parsed > 0 ? $parsed : 1;
    }

    /** @return array<string, mixed> */
    public function server(): array
    {
        return $this->server;
    }
}
