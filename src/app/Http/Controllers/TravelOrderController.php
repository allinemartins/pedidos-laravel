<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TravelOrder;
use App\Http\Requests\StoreTravelOrderRequest;
use App\Http\Requests\UpdateTravelOrderStatusRequest;
use App\Http\Resources\TravelOrderResource;
use App\Notifications\TravelOrderStatusChanged;


class TravelOrderController extends Controller
{
     public function index(Request $request)
    {
        $user = $request->user();

        $q = TravelOrder::query();
        
        if (!$user->isAdmin()) {
            $q->where('user_id', $user->id);
        }

        if ($status = $request->query('status')) {
            $q->where('status', $status);
        }

        if ($destination = $request->query('destination')) {
            $q->where('destination', 'like', "%{$destination}%");
        }
        
        //filters
        $travelFrom = $request->query('travel_from');
        $travelTo = $request->query('travel_to');
        if ($travelFrom) $q->whereDate('departure_date', '>=', $travelFrom);
        if ($travelTo)   $q->whereDate('return_date', '<=', $travelTo);
        
        $createdFrom = $request->query('created_from');
        $createdTo = $request->query('created_to');
        if ($createdFrom) $q->whereDate('created_at', '>=', $createdFrom);
        if ($createdTo)   $q->whereDate('created_at', '<=', $createdTo);

        //pagination
        $orders = $q->orderByDesc('created_at')->paginate(20);

        return TravelOrderResource::collection($orders);
    }

    public function store(StoreTravelOrderRequest $request)
    {
        $user = $request->user();

        $order = new TravelOrder($request->validated());
        $order->status = 'REQUESTED';
        $order->user()->associate($user);
        $order->save();

        return (new TravelOrderResource($order))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Request $request, string $id)
    {
        $user = $request->user();

        $order = TravelOrder::findOrFail($id);

        if (!$user->isAdmin() && $order->user_id !== $user->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        return new TravelOrderResource($order);
    }
    
    public function updateStatus(UpdateTravelOrderStatusRequest $request, string $id)
    {
        $order = TravelOrder::findOrFail($id);
        
        $newStatus = $request->validated()['status'];
        
        if ($newStatus === 'CANCELED' && $order->status === 'APPROVED') {
            return response()->json([
                'message' => 'Cannot cancel an approved order'
            ], 422);
        }

        $order->status = $newStatus;
        $order->save();

        $order->user?->notify(new TravelOrderStatusChanged($order));

        return new TravelOrderResource($order);
    }
}
