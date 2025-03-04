<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Relations\Relation;
use App\Models\Order;
use App\Models\Sale\SaleOrder;
use App\Models\Purchase\PurchaseOrder;
use App\Models\Customer;
use App\Models\OrderPayment;
use App\Models\OrderedProduct;
use App\Traits\FormatNumber;

use Illuminate\Support\Number;

use App\Models\Sale\Sale;
use App\Models\Sale\SaleReturn;
use App\Models\Purchase\Purchase;
use App\Models\Purchase\PurchaseReturn;
use App\Models\Party\Party;
use App\Models\Party\PartyTransaction;
use App\Models\Party\PartyPayment;
use App\Models\Expenses\Expense;
use App\Models\Items\ItemTransaction;



class DashboardController extends Controller
{
    use formatNumber;


    public function index()
    {

        $pendingSaleOrders          = SaleOrder::whereDoesntHave('sale')
                                                ->when(auth()->user()->can('dashboard.can.view.self.dashboard.details.only'), function ($query) {
                                                    return $query->where('created_by', auth()->user()->id);
                                                })
                                                ->count();
        $totalCompletedSaleOrders   = SaleOrder::whereHas('sale')
                                                ->when(auth()->user()->can('dashboard.can.view.self.dashboard.details.only'), function ($query) {
                                                    return $query->where('created_by', auth()->user()->id);
                                                })
                                                ->count();

        $partyBalance               = $this->paymentReceivables();
        $totalPaymentReceivables    = $this->formatWithPrecision($partyBalance['receivable']);
        $totalPaymentPaybles        = $this->formatWithPrecision($partyBalance['payable']);

        $pendingPurchaseOrders          = PurchaseOrder::whereDoesntHave('purchase')
                                                ->when(auth()->user()->can('dashboard.can.view.self.dashboard.details.only'), function ($query) {
                                                    return $query->where('created_by', auth()->user()->id);
                                                })
                                                ->count();
        $totalCompletedPurchaseOrders   = PurchaseOrder::whereHas('purchase')
                                                ->when(auth()->user()->can('dashboard.can.view.self.dashboard.details.only'), function ($query) {
                                                    return $query->where('created_by', auth()->user()->id);
                                                })
                                                ->count();

        $totalCustomers       = Party::where('party_type', 'customer')
                                                ->when(auth()->user()->can('dashboard.can.view.self.dashboard.details.only'), function ($query) {
                                                    return $query->where('created_by', auth()->user()->id);
                                                })
                                                ->count();

        $totalExpense         = Expense::when(auth()->user()->can('dashboard.can.view.self.dashboard.details.only'), function ($query) {
                                                    return $query->where('created_by', auth()->user()->id);
                                                })
                                                ->sum('grand_total');
        $totalExpense         = $this->formatWithPrecision($totalExpense);

        $recentInvoices       = Sale::when(auth()->user()->can('dashboard.can.view.self.dashboard.details.only'), function ($query) {
                                                    return $query->where('created_by', auth()->user()->id);
                                                })
                                                ->orderByDesc('id')
                                                ->limit(10)
                                                ->get();

        $saleVsPurchase       = $this->saleVsPurchase();
        $trendingItems        = $this->trendingItems();


        return view('dashboard', compact(
                                            'pendingSaleOrders',
                                            'pendingPurchaseOrders',

                                            'totalCompletedSaleOrders',
                                            'totalCompletedPurchaseOrders',

                                            'totalCustomers',
                                            'totalPaymentReceivables',
                                            'totalPaymentPaybles',
                                            'totalExpense',

                                            'saleVsPurchase',
                                            'trendingItems',
                                            'recentInvoices',
                                        ));
    }

    public function saleVsPurchase()
    {
        $labels = [];
        $sales = [];
        $purchases = [];

        $now = now();
        for ($i = 0; $i < 6; $i++) {
            $month = $now->copy()->subMonths($i)->format('M Y');
            $labels[] = $month;

            // Get value for this month, e.g. from database
            $sales[] = Sale::whereMonth('sale_date', $now->copy()->subMonths($i)->month)
                   ->whereYear('sale_date', $now->copy()->subMonths($i)->year)
                   ->when(auth()->user()->can('dashboard.can.view.self.dashboard.details.only'), function ($query) {
                        return $query->where('created_by', auth()->user()->id);
                    })
                   ->count();

            $purchases[] = Purchase::whereMonth('purchase_date', $now->copy()->subMonths($i)->month)
                   ->whereYear('purchase_date', $now->copy()->subMonths($i)->year)
                   ->when(auth()->user()->can('dashboard.can.view.self.dashboard.details.only'), function ($query) {
                        return $query->where('created_by', auth()->user()->id);
                    })
                   ->count();

        }

        $labels = array_reverse($labels);
        $sales = array_reverse($sales);
        $purchases = array_reverse($purchases);

        $saleVsPurchase = [];

        for($i = 0; $i < count($labels); $i++) {
          $saleVsPurchase[] = [
            'label'     => $labels[$i],
            'sales'     => $sales[$i],
            'purchases' => $purchases[$i],
          ];
        }

        return $saleVsPurchase;
    }

    public function trendingItems() : array
    {
        // Get top 4 trending items (adjust limit as needed)
        return ItemTransaction::query()
            ->select([
                'items.name',
                DB::raw('SUM(item_transactions.quantity) as total_quantity')
            ])
            ->join('items', 'items.id', '=', 'item_transactions.item_id')
            ->where('item_transactions.transaction_type', getMorphedModelName(Sale::class))
            ->when(auth()->user()->can('dashboard.can.view.self.dashboard.details.only'), function ($query) {
                return $query->where('item_transactions.created_by', auth()->user()->id);
            })
            ->groupBy('item_transactions.item_id', 'items.name')
            ->orderByDesc('total_quantity')
            ->limit(4)
            ->get()
            ->toArray();
    }



    public function paymentReceivables(){
        // Retrieve opening balance from PartyTransaction
        $openingBalance = PartyTransaction::selectRaw('COALESCE(SUM(to_receive) - SUM(to_pay), 0) as opening_balance')
                                            ->when(auth()->user()->can('dashboard.can.view.self.dashboard.details.only'), function ($query) {
                                                return $query->where('created_by', auth()->user()->id);
                                            })
                                            ->first()
                                            ->opening_balance ?? 0;

        // Get total amount received from customers (Sale Adjustments)
        $partyPaymentReceiveSum = PartyPayment::where('payment_direction', 'receive')
            ->leftJoin('party_payment_allocations', 'party_payments.id', '=', 'party_payment_allocations.party_payment_id')
            ->leftJoin('payment_transactions', 'party_payment_allocations.payment_transaction_id', '=', 'payment_transactions.id')
            ->selectRaw('SUM(party_payments.amount) - COALESCE(SUM(payment_transactions.amount), 0) AS total_amount')
            ->when(auth()->user()->can('dashboard.can.view.self.dashboard.details.only'), function ($query) {
                return $query->where('party_payments.created_by', auth()->user()->id);
            })
            ->value('total_amount') ?? 0;

        // Get total amount paid to suppliers (Purchase Adjustments)
        $partyPaymentPaySum = PartyPayment::where('payment_direction', 'pay')
            ->leftJoin('party_payment_allocations', 'party_payments.id', '=', 'party_payment_allocations.party_payment_id')
            ->leftJoin('payment_transactions', 'party_payment_allocations.payment_transaction_id', '=', 'payment_transactions.id')
            ->selectRaw('SUM(party_payments.amount) - COALESCE(SUM(payment_transactions.amount), 0) AS total_amount')
            ->when(auth()->user()->can('dashboard.can.view.self.dashboard.details.only'), function ($query) {
                return $query->where('party_payments.created_by', auth()->user()->id);
            })
            ->value('total_amount') ?? 0;

        // Sale balance (grand_total - paid_amount)
        $saleBalance = Sale::selectRaw('coalesce(sum(grand_total - paid_amount), 0) as total')
            ->when(auth()->user()->can('dashboard.can.view.self.dashboard.details.only'), function ($query) {
                return $query->where('created_by', auth()->user()->id);
            })
            ->value('total');

        // Sale Return balance
        $saleReturnBalance = SaleReturn::selectRaw('coalesce(sum(grand_total - paid_amount), 0) as total')
            ->when(auth()->user()->can('dashboard.can.view.self.dashboard.details.only'), function ($query) {
                return $query->where('created_by', auth()->user()->id);
            })
            ->value('total');

        // Purchase balance
        $purchaseBalance = Purchase::selectRaw('coalesce(sum(grand_total - paid_amount), 0) as total')
            ->when(auth()->user()->can('dashboard.can.view.self.dashboard.details.only'), function ($query) {
                return $query->where('created_by', auth()->user()->id);
            })
            ->value('total');

        // Purchase Return balance
        $purchaseReturnBalance = PurchaseReturn::selectRaw('coalesce(sum(grand_total - paid_amount), 0) as total')
            ->when(auth()->user()->can('dashboard.can.view.self.dashboard.details.only'), function ($query) {
                return $query->where('created_by', auth()->user()->id);
            })
            ->value('total');

        // Calculate balance for party
        $partyReceivable = $openingBalance + $partyPaymentReceiveSum + $saleBalance - $saleReturnBalance;
        $partyPayable = $partyPaymentPaySum + $purchaseBalance - $purchaseReturnBalance;

        return [
                'payable' => abs($partyPayable),
                'receivable' => abs($partyReceivable),
            ];
    }
}
