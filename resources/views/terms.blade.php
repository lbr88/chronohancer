<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Terms of Use - Chronohancer</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
        @vite(['resources/css/app.css'])
    </head>
    <body class="bg-[#FDFDFC] dark:bg-[#0a0a0a] text-[#1b1b18] min-h-screen">
            <!-- Header -->
            <header class="border-b border-zinc-200 dark:border-zinc-800">
                <div class="max-w-4xl mx-auto px-6 lg:px-8 py-6">
                    <h1 class="text-3xl font-semibold dark:text-[#EDEDEC]">Terms of Use</h1>
                </div>
            </header>
    
            <main class="max-w-4xl mx-auto px-6 lg:px-8 py-8">
                <div class="prose dark:prose-invert prose-zinc max-w-none">
                <p class="mb-4">Last updated: {{ date('F j, Y') }}</p>

                <h2 class="text-2xl font-semibold mt-8 mb-4">Agreement to Terms</h2>
                <p>By accessing and using Chronohancer ("the Application"), you agree to be bound by these Terms of Use and all applicable laws and regulations.</p>

                <h2 class="text-2xl font-semibold mt-8 mb-4">Use License</h2>
                <p>We grant you a personal, non-exclusive, non-transferable license to use the Application for time tracking purposes. You may not:</p>
                <ul class="list-disc pl-6 space-y-2">
                    <li>Modify or copy the Application materials</li>
                    <li>Use the Application for commercial purposes</li>
                    <li>Attempt to decompile or reverse engineer the Application</li>
                    <li>Remove any copyright or proprietary notations</li>
                </ul>

                <h2 class="text-2xl font-semibold mt-8 mb-4">User Responsibilities</h2>
                <p>You are responsible for:</p>
                <ul class="list-disc pl-6 space-y-2">
                    <li>Maintaining the confidentiality of your account</li>
                    <li>All activities that occur under your account</li>
                    <li>Ensuring your data is accurate and up-to-date</li>
                    <li>Complying with all applicable laws and regulations</li>
                </ul>

                <h2 class="text-2xl font-semibold mt-8 mb-4">Service Availability</h2>
                <p>We strive to provide uninterrupted service but may need to temporarily suspend access for maintenance or updates. We are not liable for any service interruptions.</p>

                <h2 class="text-2xl font-semibold mt-8 mb-4">Intellectual Property</h2>
                <p>The Application, including its original content, features, and functionality, is owned by Chronohancer and is protected by international copyright, trademark, and other intellectual property laws.</p>

                <h2 class="text-2xl font-semibold mt-8 mb-4">Termination</h2>
                <p>We may terminate or suspend your account and access to the Application immediately, without prior notice, for conduct that we believe violates these Terms or is harmful to other users or us.</p>

                <h2 class="text-2xl font-semibold mt-8 mb-4">Limitation of Liability</h2>
                <p>Chronohancer shall not be liable for any indirect, incidental, special, consequential, or punitive damages resulting from your use or inability to use the Application.</p>

                <h2 class="text-2xl font-semibold mt-8 mb-4">Changes to Terms</h2>
                <p>We reserve the right to modify these terms at any time. We will notify users of any material changes via email or through the Application.</p>

                <h2 class="text-2xl font-semibold mt-8 mb-4">Contact Us</h2>
                <p>If you have questions about these Terms of Use, please contact us at terms@chronohancer.com</p>
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