<?php

namespace SomeoneFamous\Wallets\Collections;

use Illuminate\Database\Eloquent\Collection;

class TransactionCollection extends Collection
{
    public function updateRunningBalance(): self
    {
        $earliestTransaction = $this->sortBy('created_at')->sortBy('id')->first();

        $balance = $earliestTransaction
            ->wallet
            ->transactions()
            ->whereNotIn('id', $this->pluck('id'))
            ->where('id', '<', $earliestTransaction->id)
            ->where('created_at', '<=', $earliestTransaction->created_at)
            ->sum('amount');

        $this->sort(function($a, $b) {

            return ($a->created_at === $b->created_at)
                ? $a->id <=> $b->id
                : $a->created_at <=> $b->created_at;

        })->map(function($transaction) use (&$balance) {

            $balance += $transaction->amount;
            $transaction->balance = $balance;

            return $transaction;
        });

        return $this;
    }
}

