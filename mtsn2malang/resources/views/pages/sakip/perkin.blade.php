@extends('layouts.app')
@section('title', 'PERKIN - MTsN 2 Kota Malang')
@section('content')
<x-archive-list
    :archives="$archives"
    :years="$years"
    title="Perjanjian Kinerja (PERKIN)"
    subtitle="Dokumen kesepakatan kinerja antara pimpinan dan jajaran madrasah"
    :breadcrumb="['SAKIP', 'PERKIN']" />
@endsection
