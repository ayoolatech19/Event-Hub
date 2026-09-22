<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEventRequest;
use App\Http\Requests\UpdateEventRequest;
use App\Http\Resources\EventResource;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class EventController extends Controller
{
   public function index(Request $request)
{
    $events = Event::with(['category', 'organizer'])
        ->where('status', 'published')
        ->when($request->search, function ($query, $search) {
            $query->where('title', 'like', "%{$search}%");
        })
        ->when($request->category, function ($query, $category) {
            $query->where('category_id', $category);
        })
        ->latest('date')
        ->paginate(10)
        ->withQueryString();

    return EventResource::collection($events);
}
    public function show(Event $event)
    {
        if ($event->status !== 'published') {
            abort(404);
        }

        return new EventResource($event->load(['category', 'organizer']));
    }

    public function store(StoreEventRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('banner_image')) {
            $data['banner_image'] = $request->file('banner_image')->store('banners', 'public');
        }

        $event = $request->user()->events()->create($data);

        return (new EventResource($event->load(['category', 'organizer'])))
            ->response()
            ->setStatusCode(201);
    }

    public function update(UpdateEventRequest $request, Event $event)
    {
        Gate::authorize('update', $event);

        $data = $request->validated();

        if ($request->hasFile('banner_image')) {
            if ($event->banner_image) {
                Storage::disk('public')->delete($event->banner_image);
            }
            $data['banner_image'] = $request->file('banner_image')->store('banners', 'public');
        }

        $event->update($data);

        return new EventResource($event->load(['category', 'organizer']));
    }

    public function destroy(Event $event)
    {
        Gate::authorize('delete', $event);

        if ($event->banner_image) {
            Storage::disk('public')->delete($event->banner_image);
        }

        $event->delete();

        return response()->json(['message' => 'Event deleted successfully.']);
    }
}