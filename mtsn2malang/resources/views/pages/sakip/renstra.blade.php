@extends('layouts.app')
@section('title', 'RENSTRA - MTsN 2 Kota Malang')
@section('content')
<x-archive-list
    :archives="$archives"
    :years="$years"
    title="Rencana Strategis (RENSTRA)"
    subtitle="Dokumen perencanaan strategis madrasah untuk periode lima tahunan"
    :breadcrumb="['SAKIP', 'RENSTRA']" />
@endsection
