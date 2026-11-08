@extends('layouts.app', [
    'title' => $page->title,
    'description' => $page->subtitle ?: strip_tags((string) $page->content),
    'bgClass' => 'bg-linear-to-br from-[#effaf8] to-[#eef5ff]',
])

@section('content')
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-14 w-full">
        <x-page-header
            variant="centered"
            :title="$page->title"
            :subtitle="$page->subtitle"
        />

        <article class="mt-10 bg-white rounded-2xl shadow-lg p-6 sm:p-10 border border-white/80">
            <div class="prose prose-slate max-w-none prose-headings:text-[#181e2b] prose-headings:font-bold prose-p:text-gray-700 prose-p:leading-relaxed prose-a:text-[#16a34a] hover:prose-a:text-[#15803d] prose-blockquote:border-l-[#16a34a] prose-blockquote:bg-[#ecfdf5]/60 prose-blockquote:py-2.5 prose-blockquote:px-5 prose-blockquote:rounded-r-xl prose-li:text-gray-700">
                {!! $page->content !!}
            </div>
        </article>

        <div class="mt-12 h-px max-w-3xl mx-auto bg-linear-to-r from-transparent via-green-500 to-transparent"></div>
    </div>
@endsection
