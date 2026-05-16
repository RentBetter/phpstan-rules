<?php

namespace App\Service;

final class TargetService
{
    public function __construct(
        public string $name = '',
        public ?int $id = null,
        public bool $active = false,
    ) {}

    public function process(int $a, int $b, int $c): void {}

    public static function build(string $name, ?int $id = null, bool $flush = false): self
    {
        return new self();
    }
}

final class Caller
{
    public function example(TargetService $svc): void
    {
        $svc->process(a: 1, b: 2, c: 3);
        $svc->process(a: 1, c: 3);
        $svc->process(b: 2, a: 1);
        $svc->process(c: 3, b: 2, a: 1);
        $svc->process(1, c: 3, b: 2);

        TargetService::build(id: 1, name: 'x');
        TargetService::build(name: 'x', flush: true);

        new TargetService(id: 1, name: 'x');
        new TargetService(name: 'x', active: true);
    }
}
