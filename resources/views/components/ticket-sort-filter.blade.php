<div class="col-12 col-md-auto">
    <label class="visually-hidden" for="ticket-sort">Urutkan laporan</label>
    <select name="sort" id="ticket-sort" class="form-select">
        <option value="submission" {{ request('sort', 'submission') === 'submission' ? 'selected' : '' }}>Laporan terbaru</option>
        <option value="schedule" {{ request('sort') === 'schedule' ? 'selected' : '' }}>Jadwal kegiatan terdekat</option>
    </select>
</div>
