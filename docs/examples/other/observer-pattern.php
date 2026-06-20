<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\IO;
use Psl\Observer;
use Psl\Vec;

final class Inventory implements Observer\SubjectInterface
{
    /** @var list<Observer\ObserverInterface<Inventory>> */
    private array $observers = [];
    private int $stock = 0;

    public function subscribe(Observer\ObserverInterface<Inventory> $observer): void
    {
        $this->observers[] = $observer;
    }

    public function unsubscribe(Observer\ObserverInterface<Inventory> $observer): void
    {
        $this->observers = Vec\filter::<Observer\ObserverInterface<Inventory>>($this->observers, static fn($o) => $o !== $observer);
    }

    public function notify(): void
    {
        foreach ($this->observers as $observer) {
            $observer->update($this);
        }
    }

    public function restock(int $quantity): void
    {
        $this->stock += $quantity;
        $this->notify();
    }

    public function getStock(): int
    {
        return $this->stock;
    }
}

/** @implements Observer\ObserverInterface<Inventory> */
final class StockAlert implements Observer\ObserverInterface
{
    /** @param Inventory $subject */
    public function update(Observer\SubjectInterface $subject): void
    {
        if ($subject->getStock() > 100) {
            IO\write_line('Alert: stock is now %d (above threshold)', $subject->getStock());
        }
    }
}

// Wiring it together
$inventory = new Inventory();
$alert = new StockAlert();

$inventory->subscribe($alert);
$inventory->restock(150); // StockAlert::update() is called
$inventory->unsubscribe($alert);
$inventory->restock(50); // No alert -- observer was removed

IO\write_line('Final stock: %d', $inventory->getStock());
