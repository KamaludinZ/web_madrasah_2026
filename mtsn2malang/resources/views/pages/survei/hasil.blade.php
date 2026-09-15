@extends('layouts.app')
@section('title', 'Publikasi Hasil Survei - MTsN 2 Kota Malang')
@section('content')
<x-archive-list
    :archives="$archives"
    :years="$years"
    title="Publikasi Hasil Survei"
    subtitle="Hasil survei kepuasan masyarakat dan persepsi anti korupsi"
    :breadcrumb="['Survei', 'Hasil Survei']" />
@endsection
