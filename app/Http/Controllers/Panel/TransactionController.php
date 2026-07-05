<?php

namespace App\Http\Controllers\Panel;

use App\Enums\TransactionType;
use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\Transaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TransactionController extends Controller
{
    public function index(Business $business, Request $request): View
    {
        $filters = $request->validate([
            'type' => ['nullable', Rule::enum(TransactionType::class)],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $from = $filters['from'] ?? today()->startOfMonth()->toDateString();
        $to = $filters['to'] ?? today()->toDateString();

        $query = Transaction::query()
            ->whereBetween('transaction_date', [$from, $to])
            ->when($filters['type'] ?? null, fn ($q, $t) => $q->where('type', $t));

        $totals = [
            'income' => (float) (clone $query)->income()->sum('amount'),
            'expense' => (float) (clone $query)->expense()->sum('amount'),
        ];
        $totals['net'] = $totals['income'] - $totals['expense'];

        $transactions = $query
            ->with(['customer:id,name', 'appointment:id'])
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        return view('panel.transactions', [
            'business' => $business,
            'transactions' => $transactions,
            'totals' => $totals,
            'from' => $from,
            'to' => $to,
            'type' => $filters['type'] ?? null,
            'incomeCategories' => Transaction::incomeCategories(),
            'expenseCategories' => Transaction::expenseCategories(),
        ]);
    }

    public function store(Business $business, Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        $transaction = new Transaction($data);
        $transaction->business_id = $business->id;
        $transaction->created_by = $request->user()->id;
        $transaction->save();

        return back()->with('success', 'Kayıt eklendi.');
    }

    public function update(Business $business, Transaction $transaction, Request $request): RedirectResponse
    {
        $transaction->update($this->validated($request));

        return back()->with('success', 'Kayıt güncellendi.');
    }

    public function destroy(Business $business, Transaction $transaction): RedirectResponse
    {
        $transaction->delete();

        return back()->with('success', 'Kayıt silindi.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'type' => ['required', Rule::enum(TransactionType::class)],
            'category' => ['nullable', 'string', 'max:50'],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:9999999'],
            'description' => ['nullable', 'string', 'max:255'],
            'transaction_date' => ['required', 'date'],
            'payment_method' => ['required', Rule::in(['cash', 'card', 'transfer', 'other'])],
        ]);
    }
}
