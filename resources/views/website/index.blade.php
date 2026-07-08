@extends('website.layouts.weblayout')

@section('title', __('writ.title'))

@section('content')

<!-- HERO -->
    <section class="hero" id="home">
        <div class="container" data-aos="fade-up">
            <h1 class="display-4 fw-bold">{{ __('writ.hero.title') }}</h1>
            <p class="lead mt-3 mb-4">{{ __('writ.hero.subtitle') }}</p>
            <a href="{{ route('lawyer.register') }}" class="btn btn-gold btn-lg">{{ __('writ.hero.cta') }}</a>
        </div>
    </section>

    <!-- ABOUT -->
    <section id="about" class="py-5">
        <div class="container" data-aos="fade-up">
            <h2 class="section-title text-center">{{ __('writ.about.title') }}</h2>
            <p class="text-center mb-4">{{ __('writ.about.text') }}</p>
        </div>
    </section>

    <!-- FEATURES -->
    <section id="features" class="py-5 bg-light">
        <div class="container">
            <h2 class="section-title text-center" data-aos="fade-up">{{ __('writ.features.title') }}</h2>
            <div class="row g-4 mt-4">
                <div class="col-md-4" data-aos="fade-right">
                    <div class="feature-box">
                        <h5 class="fw-bold">{{ __('writ.features.f1_title') }}</h5>
                        <p>{{ __('writ.features.f1_text') }}</p>
                    </div>
                </div>
                <div class="col-md-4" data-aos="fade-up">
                    <div class="feature-box">
                        <h5 class="fw-bold">{{ __('writ.features.f2_title') }}</h5>
                        <p>{{ __('writ.features.f2_text') }}</p>
                    </div>
                </div>
                <div class="col-md-4" data-aos="fade-left">
                    <div class="feature-box">
                        <h5 class="fw-bold">{{ __('writ.features.f3_title') }}</h5>
                        <p>{{ __('writ.features.f3_text') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- HOW IT WORKS -->
    <section id="process" class="py-5 steps">
        <div class="container">
            <h2 class="section-title text-center" data-aos="fade-up">{{ __('writ.process.title') }}</h2>
            <div class="row text-center g-4 mt-4">
                <div class="col-md-4" data-aos="zoom-in">
                    <div class="feature-box">
                        <span class="badge bg-primary rounded-pill mb-2">1</span>
                        <h5 class="fw-bold">{{ __('writ.process.step1_title') }}</h5>
                        <p>{{ __('writ.process.step1_text') }}</p>
                    </div>
                </div>
                <div class="col-md-4" data-aos="zoom-in" data-aos-delay="150">
                    <div class="feature-box">
                        <span class="badge bg-primary rounded-pill mb-2">2</span>
                        <h5 class="fw-bold">{{ __('writ.process.step2_title') }}</h5>
                        <p>{{ __('writ.process.step2_text') }}</p>
                    </div>
                </div>
                <div class="col-md-4" data-aos="zoom-in" data-aos-delay="300">
                    <div class="feature-box">
                        <span class="badge bg-primary rounded-pill mb-2">3</span>
                        <h5 class="fw-bold">{{ __('writ.process.step3_title') }}</h5>
                        <p>{{ __('writ.process.step3_text') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- BENEFITS -->
    <section id="benefits" class="py-5">
        <div class="container">
            <h2 class="section-title text-center" data-aos="fade-up">{{ __('writ.benefits.title') }}</h2>
            <div class="row g-4 mt-3 text-center">
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="50">
                    <div class="feature-box">
                        <h5>{{ __('writ.benefits.b1_title') }}</h5>
                        <p>{{ __('writ.benefits.b1_text') }}</p>
                    </div>
                </div>
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="150">
                    <div class="feature-box">
                        <h5>{{ __('writ.benefits.b2_title') }}</h5>
                        <p>{{ __('writ.benefits.b2_text') }}</p>
                    </div>
                </div>
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="250">
                    <div class="feature-box">
                        <h5>{{ __('writ.benefits.b3_title') }}</h5>
                        <p>{{ __('writ.benefits.b3_text') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ -->
    <section id="faq" class="py-5 bg-light">
        <div class="container" data-aos="fade-up">
            <h2 class="section-title text-center mb-4">{{ __('writ.faq.title') }}</h2>
            @php
                $faqItems = __('writ.faq.items');
                if (! is_array($faqItems)) {
                    $faqItems = [
                        ['q' => 'How do I register as a lawyer?', 'a' => 'Use the register button and fill in the required information.'],
                        ['q' => 'Can I track writ file status online?', 'a' => 'Yes, authorized users can track file movement and status updates.'],
                        ['q' => 'Is file movement recorded?', 'a' => 'Yes, each movement is recorded for tracking and accountability.'],
                    ];
                }
            @endphp
            <div class="accordion" id="faqAccordion">
                @foreach ($faqItems as $key => $faq)
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="faq{{ $loop->index }}">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                data-bs-target="#collapse{{ $loop->index }}">
                                {{ $faq['q'] ?? '' }}
                            </button>
                        </h2>
                        <div id="collapse{{ $loop->index }}" class="accordion-collapse collapse"
                            data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                {{ $faq['a'] ?? '' }}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- CTA -->
    <section class="py-5 text-center">
        <div class="container" data-aos="fade-up">
            <h2 class="mb-3">{{ __('writ.cta.title') }}</h2>
            <p class="mb-4">{{ __('writ.cta.text') }}</p>
            <a href="{{ route('lawyer.register') }}" class="btn btn-gold btn-lg me-3">{{ __('writ.cta.register') }}</a>
            <a href="{{ route('lawyer.login') }}" class="btn btn-outline-primary btn-lg">{{ __('writ.cta.login') }}</a>
        </div>
    </section>

    @endsection
