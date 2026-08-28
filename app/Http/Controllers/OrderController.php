<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with(['orderItems', 'payment']);
        
        // Example filter for customer view or admin view
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $orders = $query->orderBy('created_at', 'desc')->paginate(10);

        return response()->json([
            'success' => true,
            'message' => 'Berhasil mengambil daftar pesanan',
            'data' => $orders
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // According to PRD 24, we must hit database to get price
        $subtotal = 0;
        $orderItems = [];

        foreach ($request->items as $item) {
            $product = Product::find($item['product_id']);
            
            if (!$product || !$product->is_available || $product->stock < $item['quantity']) {
                return response()->json([
                    'success' => false,
                    'message' => "Produk tidak tersedia atau stok tidak cukup"
                ], 400);
            }

            $itemSubtotal = $product->price * $item['quantity'];
            $subtotal += $itemSubtotal;

            $orderItems[] = [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'price' => $product->price,
                'quantity' => $item['quantity'],
                'subtotal' => $itemSubtotal,
            ];
        }

        $serviceFee = 2000; // As per PRD 11
        $total = $subtotal + $serviceFee;

        // Create Order
        $order = Order::create([
            'user_id' => $request->user() ? $request->user()->id : null,
            'order_number' => 'ORD-' . strtoupper(Str::random(6)) . time(),
            'customer_name' => $request->customer_name,
            'phone' => $request->phone,
            'address' => $request->address,
            'notes' => $request->notes,
            'subtotal' => $subtotal,
            'service_fee' => $serviceFee,
            'total' => $total,
            'payment_status' => 'pending',
            'order_status' => 'pending',
        ]);

        // Create Order Items
        foreach ($orderItems as $item) {
            $order->orderItems()->create($item);
        }

        // In the next phase, we will integrate Midtrans here

        return response()->json([
            'success' => true,
            'message' => 'Order berhasil dibuat',
            'data' => $order->load('orderItems')
        ], 201);
    }

    public function show(string $id)
    {
        $order = Order::with(['orderItems', 'payment'])->find($id);

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Pesanan tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Berhasil mengambil detail pesanan',
            'data' => $order
        ]);
    }
}
