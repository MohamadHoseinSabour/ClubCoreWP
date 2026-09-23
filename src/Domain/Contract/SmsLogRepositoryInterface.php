<?php
declare(strict_types=1);

namespace ClubCore\Domain\Contract;

interface SmsLogRepositoryInterface
{
    public function create(array $data): int;

    public function findByMember(int $memberId, array $args = []): array;

    public function list(array $args = []): array;

    public function count(array $filters = []): int;
}
