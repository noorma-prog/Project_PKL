<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TeknisiController extends Controller
{
    /**
     * Dashboard Teknisi (Hak Akses Read-Only)
     */
    public function dashboard()
    {
        $technician = Auth::user();

        // Mengambil ID Padal tempat teknisi ini terdaftar sebagai anggota (PADAL_ANGGOTA)
        $padalIds = $technician->padalTeams()->pluck('padals.id')->toArray();

        $ticketQuery = Ticket::whereIn('padal_id', $padalIds);

        $stats = [
            'total_penugasan' => (clone $ticketQuery)->count(),
            'dalam_penanganan' => (clone $ticketQuery)->where('status', Ticket::STATUS_DALAM_PENANGANAN)->count(),
            'selesai' => (clone $ticketQuery)->whereIn('status', [Ticket::STATUS_SELESAI, Ticket::STATUS_SELESAI_DINILAI])->count(),
            'padal_teams' => $technician->padalTeams()->count(),
        ];

        $recentAssignedTickets = (clone $ticketQuery)
            ->with(['user', 'category', 'padal.leader'])
            ->latest()
            ->take(5)
            ->get();

        $myPadals = $technician->padalTeams()->with('leader')->get();

        return view('teknisi.dashboard', compact('stats', 'recentAssignedTickets', 'myPadals'));
    }

    /**
     * Daftar seluruh tiket yang ditangani oleh unit Padal tempat teknisi terdaftar
     */
    public function tickets(Request $request)
    {
        $technician = Auth::user();
        $padalIds = $technician->padalTeams()->pluck('padals.id')->toArray();

        $query = Ticket::with(['user', 'category', 'padal.leader', 'rating'])
            ->whereIn('padal_id', $padalIds);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('ticket_number', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%");
            });
        }

        $tickets = $query->latest()->paginate(10)->withQueryString();
        $categories = Category::where('is_active', true)->get();

        return view('teknisi.tickets.index', compact('tickets', 'categories'));
    }

    /**
     * Detail tiket (Hak Akses Read-Only murni untuk Teknisi Sesuai Use Case: Melihat Detail Tiket)
     */
    public function showTicket($id)
    {
        $technician = Auth::user();
        $padalIds = $technician->padalTeams()->pluck('padals.id')->toArray();

        // Teknisi hanya dapat melihat tiket dari Padal tempat ia menjadi anggota
        $ticket = Ticket::with([
            'user', 
            'category', 
            'padal.leader', 
            'padal.members', 
            'diperiksaOleh', 
            'ditugaskanOleh', 
            'rating', 
            'histories.user'
        ])->whereIn('padal_id', $padalIds)->findOrFail($id);

        return view('teknisi.tickets.show', compact('ticket'));
    }
}
