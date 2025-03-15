<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Privacy Policy - Chronohancer</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
        @vite(['resources/css/app.css'])
    </head>
    <body class="bg-[#FDFDFC] dark:bg-[#0a0a0a] text-[#1b1b18] min-h-screen">
            <!-- Header -->
            <header class="border-b border-zinc-200 dark:border-zinc-800">
                <div class="max-w-4xl mx-auto px-6 lg:px-8 py-6">
                    <h1 class="text-3xl font-semibold dark:text-[#EDEDEC]">Privacy Policy</h1>
                </div>
            </header>
    
            <main class="max-w-4xl mx-auto px-6 lg:px-8 py-8">
                <div class="prose dark:prose-invert prose-zinc max-w-none">
                <p class="mb-4">Last updated: {{ date('F j, Y') }}</p>

                <h2 class="text-2xl font-semibold mt-8 mb-4">Introduction</h2>
                <p>This Privacy Policy describes how Chronohancer ("we", "us", or "our") collects, uses, and protects your personal information when you use our time tracking application.</p>

                <h2 class="text-2xl font-semibold mt-8 mb-4">Information We Collect</h2>
                <ul class="list-disc pl-6 space-y-2">
                    <li>Account information (email, name)</li>
                    <li>Time tracking data (projects, tasks, time entries)</li>
                    <li>Usage information (app interactions, preferences)</li>
                </ul>

                <h2 class="text-2xl font-semibold mt-8 mb-4">How We Use Your Information</h2>
                <ul class="list-disc pl-6 space-y-2">
                    <li>To provide and maintain our service</li>
                    <li>To send important updates and notifications</li>
                </ul>

                <h2 class="text-2xl font-semibold mt-8 mb-4">Data Security</h2>
                <p>We implement appropriate security measures to protect your personal information from unauthorized access, alteration, or disclosure.</p>

                <h2 class="text-2xl font-semibold mt-8 mb-4">Third-Party Services</h2>
                <p>We use no third party services. Except if you enable integrations and chose to sync information to these integrations.</p>

                <h2 class="text-2xl font-semibold mt-8 mb-4">Your Rights</h2>
                <p>You have the right to:</p>
                <ul class="list-disc pl-6 space-y-2">
                    <li>Access your personal data</li>
                    <li>Correct inaccurate data</li>
                    <li>Request deletion of your data</li>
                    <li>Object to data processing</li>
                </ul>

                <h2 class="text-2xl font-semibold mt-8 mb-4">Contact Us</h2>
                <p>If you have questions about this Privacy Policy, please contact us at privacy@chronohancer.com</p>
            </div>
        </main>

        <!-- Footer -->
        <footer class="max-w-4xl mx-auto px-6 lg:px-8 py-8 border-t border-zinc-200 dark:border-zinc-800">
            <a href="{{ url('/') }}" class="ch-btn-secondary">
                ← Back to Home
            </a>
        </footer>
    </body>
</html>