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

        $ticketQuery = Ticket::whereHas('technicians', function ($query) use ($technician) {
            $query->where('users.id', $technician->id);
        });

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
     * Daftar tiket yang secara langsung ditugaskan kepada teknisi ini
     */
    public function tickets(Request $request)
    {
        $technician = Auth::user();

        $query = Ticket::with(['user', 'category', 'padal.leader', 'rating'])
            ->whereHas('technicians', function ($technicianQuery) use ($technician) {
                $technicianQuery->where('users.id', $technician->id);
            });

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
     * Detail tiket yang ditugaskan langsung (hak akses read-only)
     */
    public function showTicket($id)
    {
        $technician = Auth::user();

        $ticket = $technician->technicianTickets()->with([
            'user', 
            'category', 
            'padal.leader', 
            'padal.members', 
            'diperiksaOleh', 
            'ditugaskanOleh', 
            'rating', 
            'histories.user',
            'technicians',
        ])->findOrFail($id);

        return view('teknisi.tickets.show', compact('ticket'));
    }
}
