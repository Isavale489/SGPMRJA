@extends('layouts.public')

@section('title', 'Inicio')

@section('content')
@include('components.carousel')
@include('components.features')
@include('components.testimonial')
@include('components.blog')
@endsection 