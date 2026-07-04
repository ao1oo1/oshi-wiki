<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContactMessageController extends Controller
{
    private function authorizeSuperAdmin(): void
    {
        abort_unless(auth()->user()?->is_super_admin, 403);
    }

    public function index(Request $request): View
    {
        $this->authorizeSuperAdmin();

        $category = $request->input('category');
        $readStatus = $request->input('read_status');

        $messages = ContactMessage::query()
            ->when($category, function ($query) use ($category) {
                $query->where('category', $category);
            })
            ->when($readStatus === 'unread', function ($query) {
                $query->where('is_read', false);
            })
            ->when($readStatus === 'read', function ($query) {
                $query->where('is_read', true);
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.contact_messages.index', [
            'messages' => $messages,
            'category' => $category,
            'readStatus' => $readStatus,
        ]);
    }

    public function show(ContactMessage $contactMessage): View
    {
        $this->authorizeSuperAdmin();

        if (! $contactMessage->is_read) {
            $contactMessage->update(['is_read' => true]);
        }

        return view('admin.contact_messages.show', [
            'message' => $contactMessage,
        ]);
    }

    public function markUnread(ContactMessage $contactMessage): RedirectResponse
    {
        $this->authorizeSuperAdmin();

        $contactMessage->update(['is_read' => false]);

        return redirect()
            ->route('admin.contact-messages.index')
            ->with('success', '未読に戻しました。');
    }

    public function destroy(ContactMessage $contactMessage): RedirectResponse
    {
        $this->authorizeSuperAdmin();

        $contactMessage->delete();

        return redirect()
            ->route('admin.contact-messages.index')
            ->with('success', 'お問い合わせに削除フラグを付けました。');
    }
}
