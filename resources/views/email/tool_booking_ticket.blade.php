<h2>Tiket Peminjaman Alat</h2>
<p>Halo {{ $toolRequest->name }},</p>
<p>Peminjaman alat Anda berhasil dibuat.</p>
<p><b>Kode Tiket:</b> {{ $toolRequest->ticket_code }}</p>
<p><b>Tanggal Pinjam:</b> {{ $toolRequest->borrow_date }}</p>
<p><b>Keperluan:</b> {{ $toolRequest->purpose }}</p>