@extends('errors::minimal')

@section('title', __('Page Expired'))
@section('code', '419')
@section('message', 'In a while you did not do anything. For security reasons please re-LOGIN. Thank you.')
{{-- @section('message', __('Page Expired')) --}}
