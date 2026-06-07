<?php

use Livewire\Component;
use App\Models\Notification;
use Livewire\Attributes\On;

new class extends Component
{
    public function getNotificationsProperty()
    {
        return Notification::forRecipient('hr', auth()->id())
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
    }

    public function getUnreadCountProperty()
    {
        return Notification::forRecipient('hr', auth()->id())
            ->unread()
            ->count();
    }

    public function markAsRead($id, $type, $data)
    {
        $notification = Notification::find($id);
        if ($notification) {
            $notification->update(['is_read' => true]);
        }

        if ($type === 'new_application' && isset($data['application_id'])) {
            return redirect()->route('hr.applications.show', $data['application_id']);
        }
    }

    public function markAllAsRead()
    {
        Notification::forRecipient('hr', auth()->id())
            ->unread()
            ->update(['is_read' => true]);
    }
};
?>

<div class="inline-flex items-center justify-center">
    <flux:dropdown position="bottom" align="end">
        <flux:button variant="ghost" icon="bell" class="relative" square>
            @if($this->unreadCount > 0)
                <span class="absolute top-1.5 right-1.5 flex h-2.5 w-2.5">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-red-500"></span>
                </span>
            @endif
        </flux:button>

        <flux:menu class="w-80 max-h-96 overflow-y-auto">
            <div class="flex items-center justify-between px-4 py-2 border-b border-zinc-100 dark:border-zinc-700">
                <span class="text-sm font-semibold">Notifikasi</span>
                @if($this->unreadCount > 0)
                    <button wire:click="markAllAsRead" class="text-xs text-indigo-600 hover:underline">Tandai semua dibaca</button>
                @endif
            </div>

            @forelse($this->notifications as $notif)
                <flux:menu.item wire:click="markAsRead({{ $notif->id }}, '{{ $notif->type }}', {{ json_encode($notif->data) }})" class="flex flex-col items-start {{ $notif->is_read ? 'opacity-70' : 'font-semibold' }}">
                    <div class="text-sm font-medium">{{ $notif->title }}</div>
                    <div class="text-xs text-zinc-500 mt-0.5 whitespace-normal leading-snug">{{ $notif->body }}</div>
                    <div class="text-[10px] text-zinc-400 mt-1">{{ $notif->created_at->diffForHumans() }}</div>
                </flux:menu.item>
            @empty
                <div class="px-4 py-6 text-center text-sm text-zinc-500">
                    Belum ada notifikasi
                </div>
            @endforelse
        </flux:menu>
    </flux:dropdown>
</div>