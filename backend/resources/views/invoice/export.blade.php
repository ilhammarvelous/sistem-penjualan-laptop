<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Export Pdf</title>
    {{-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous"> --}}
    <link rel="stylesheet" href="{{ public_path('css/bootstrap.min.css') }}">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-image: url('/assets/img/FKN.png');
            background-size: cover;
            background-repeat: no-repeat;
            background-attachment: fixed;
            background-position: center;
        }

        header {
            margin-top: -45px;
            margin-left: -45px;
            margin-right: -45px;
        }

        .table-borderless td,
        .table-borderless th {
            border: none;
        }

        .table-bordered td,
        .table-bordered th {
            border: 2px solid black;
        }

        .highlight {
            background-color: yellow;
        }

        .judul {
            margin-top: -50px;
        }

        .kode-date {
            margin-top: 100px;
        }

        .garis {
            margin-top: -90px;
            width: 100%;
            height: 1px;
            background-color: #c4c3c3;
        }

        .garis2 {
            width: 100%;
            height: 1px;
            background-color: #c4c3c3;
        }

        section {
            width: 94%;
            margin-left: 20px;
        }

        .invoice {
            margin-top: -100px;
        }

        .invoice p {
            margin-top: -7px;
        }

        .invoice h3 span {
            font-size: 25px;
            font-family: Arial, sans-serif;
        }

        .row {
            display: flexbox;
            width: 100%;
            overflow: hidden;
        }

        .table-custom th {
            background-color: #0940ae;
            color: white;
        }

        .table-custom td,
        .table-custom th {
            padding: 10px;
        }

        .contact-info {
            margin-top: 55px;
        }

        .contact-item {
            margin-bottom: 10px;
        }

        footer {
            margin-top: -100px;
            margin-left: -45px;
            margin-right: -45px;
            page-break-inside: avoid;
        }

        .bg {
            position: absolute;
            top: 0;
            left: 0;
            margin-top: -45px;
            margin-left: -45px;
            width: 210mm;
            height: 297mm;
            z-index: -1;
        }

        section {
            margin-top: 80;
        }
    </style>
</head>

<body>
    <div class="bg">
        @php
        $path = public_path('assets/img/FKN.png');
        $type = pathinfo($path, PATHINFO_EXTENSION);
        $data = file_get_contents($path);
        $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
        @endphp
        <img src="{{ $base64 }}" width="100%" height="100%" />
    </div>

    <section>
        <div class="judul">
            <div class="row">
                <div style="float: left; width: 50%;">
                    <div class="logo">
                        @php
                            $path = public_path('assets/img/logo.png');
                            $type = pathinfo($path, PATHINFO_EXTENSION);
                            $data = file_get_contents($path);
                            $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
                        @endphp
                        <img src="{{ $base64 }}" height="80" />
                    </div>
                </div>
                <div style="float: right; width: 50%; margin-top: 10px;">
                    <p style="font-size: 40px; font-weight: bold; margin-left: 177px;">INVOICE</p>
                </div>
                <div style="clear: both;"></div>
            </div>
        </div>

        <div class="kode-date">
            <div class="garis"></div>
            <p style="margin-top: 15px;">{{ $transaksi->id_transaksi }}</p>
            <p style="margin-top: -40px;" class="text-end">TANGGAL: {{ $transaksi->tanggal }}</p>
            <div class="garis2"></div><br>
        </div>

        <div class="payable-to-bank-details">
            <div class="payable-to">
                <p style="font-weight: bold;">Dibayarkan kepada</p>
                <p style="margin-top: -5px;">{{$transaksi->nama}}</p>
                <p style="margin-top: -10px;">{{ $transaksi->alamat }}</p>
            </div>
            <div class="bank-details text-end" style="margin-top: -100px;">
                <p style="font-weight: bold; ">BANK DETAILS</p>
                <p style="margin-top: -5px;">BCA - 12345678910</p>
                <p style="margin-top: -10px;">BAGUS RAMDANA</p>
            </div>
        </div>

        <div class="table-responsive mt-4">
            <table class="table table-custom">
                <thead>
                    <tr>
                        <th style="border-top-left-radius: 5px; border-bottom-left-radius: 5px;">DESKRIPSI BARANG</th>
                        <th style="margin-left: -5px;">QUANTITY</th>
                        <th>HARGA</th>
                        <th style="border-top-right-radius: 5px; border-bottom-right-radius: 5px;">SUBTOTAL</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($detail as $barang)
                    <tr style="font-size: 15px;">
                        <td>{{ $barang->nama_barang }}</td>
                        <td>{{ $barang->quantity }}</td>
                        <td>@rupiah($barang->harga)</td>
                        <td>@rupiah($barang->subtotal)</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <div class="contact-info">
            <p style="font-weight: bold; ">CONTACT INFO :</p>
            <div class="contact-item">
                @php
                $path = public_path('assets/img/phone.png');
                $type = pathinfo($path, PATHINFO_EXTENSION);
                $data = file_get_contents($path);
                $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
                @endphp
                <img src="{{ $base64 }}" width="24" height="24" />
                <span style="margin-left: 5px;">0822 3657 2750</span>
            </div>
            <div class="contact-item">
                @php
                $path = public_path('assets/img/maps.png');
                $type = pathinfo($path, PATHINFO_EXTENSION);
                $data = file_get_contents($path);
                $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
                @endphp
                <img src="{{ $base64 }}" width="24" height="24" />
                <span style="margin-left: 5px; ">Maspion IT Surabaya <br> Jln Ahmad Yani No 73 Margorejo Lt 1 Blok F 12, <br> Surabaya, Jawa Timur</span>
            </div>
        </div>

        <div class="total" style="margin-top: -120px;">
            {{-- <div class="sub-total">
                <p class="text-center" style="font-weight: bold; margin-left: 130px;">SUB TOTAL</p>
                <p class="text-end" style="margin-top: -50px;">@rupiah($detail->total)</p>
            </div> --}}
            {{-- <div class="down-payment">
                <p class="text-center" style="font-weight: bold; margin-left: 170px;">DOWN PAYMENT</p>
                <p class="text-end" style="margin-top: -50px;">@rupiah($invoice->down_payment)</p>
            </div> --}}
            <div class="down-payment">
                <p class="text-center" style="font-weight: bold;  margin-left: 155px;">TOTAL PEMBAYARAN</p>
                <p class="text-end" style="margin-top: -50px;">@rupiah($transaksi->total_pembayaran)</p>
            </div>
        </div>

        <div>
            <p style="font-weight: bold; font-size: 22px; margin-left: 420px; margin-top: 70px;">THANK YOU!</p><br><br>
            <p style="font-size: 15px; margin-left: 440px; margin-top: 40px;">Administrator</p>
        </div>
    </section>
</body>
</html>