@extends('layouts.app')
@section('title', 'Laporan Hasil Pengaduan - MTsN 2 Kota Malang')
@section('content')
<x-archive-list
    :archives="$archives"
    :years="$years"
    title="Laporan Tindak Lanjut Pengaduan"
    subtitle="Rekapitulasi dan tindak lanjut pengaduan masyarakat"
    :breadcrumb="['Pengaduan', 'Laporan Tindak Lanjut']" />
@endsection
