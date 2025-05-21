<h2>Tiket Peminjaman Ruang</h2>
<p>Halo {{ $roomRequest->name }},</p>
<p>Peminjaman ruang Anda berhasil dibuat.</p>
<p><b>Kode Tiket:</b> {{ $roomRequest->ticket_code }}</p>
<p><b>Tanggal Pinjam:</b> {{ $roomRequest->borrow_date }}</p>
<p><b>Jam:</b> {{ $roomRequest->start_time }} - {{ $roomRequest->end_time }}</p>
<p><b>Keperluan:</b> {{ $roomRequest->purpose }}</p>