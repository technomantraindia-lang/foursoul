@php
    $companyName = config('app.name', 'FourSouls');
    $supportEmail = config('settings.support.email', 'support@foursouls.in');
    $year = now()->year;
@endphp
<footer class="site-footer bg-[#091724] text-white border-t border-white/10 pt-12 pb-8 mt-auto">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <!-- MOBILE VIEW (matches sample image layout) -->
        <div class="block md:hidden text-center pb-6">
            <div class="inline-flex items-center justify-center bg-white rounded-2xl px-6 py-3 shadow-xl mb-4">
                <span class="text-xl font-bold tracking-widest text-[#091724]">FOURSOULS</span>
            </div>
            <p class="text-sm text-slate-400 max-w-sm mx-auto mb-5 leading-relaxed">
                A sanctuary for holistic wellness, transformative yoga, and conscious mindful living — nurturing mind, body, and spirit.
            </p>
            <!-- Social Circles -->
            <div class="flex justify-center gap-3 mb-6">
                <a href="https://linkedin.com" target="_blank" class="w-10 h-10 rounded-full border border-white/20 bg-white/5 flex items-center justify-center text-xs font-bold text-slate-200 hover:bg-[#e8a838] hover:text-[#091724] transition">in</a>
                <a href="https://x.com" target="_blank" class="w-10 h-10 rounded-full border border-white/20 bg-white/5 flex items-center justify-center text-xs font-bold text-slate-200 hover:bg-[#e8a838] hover:text-[#091724] transition">X</a>
                <a href="https://facebook.com" target="_blank" class="w-10 h-10 rounded-full border border-white/20 bg-white/5 flex items-center justify-center text-xs font-bold text-slate-200 hover:bg-[#e8a838] hover:text-[#091724] transition">f</a>
                <a href="https://instagram.com" target="_blank" class="w-10 h-10 rounded-full border border-white/20 bg-white/5 flex items-center justify-center text-xs font-bold text-slate-200 hover:bg-[#e8a838] hover:text-[#091724] transition">ig</a>
                <a href="https://youtube.com" target="_blank" class="w-10 h-10 rounded-full border border-white/20 bg-white/5 flex items-center justify-center text-xs font-bold text-slate-200 hover:bg-[#e8a838] hover:text-[#091724] transition">yt</a>
            </div>
            <!-- Quick Links Box with Pill Buttons -->
            <div class="bg-[#0e1d2c] border border-white/10 rounded-2xl p-5 text-left shadow-2xl mb-6">
                <div class="mb-4">
                    <span class="text-xs font-extrabold tracking-widest text-[#e8a838] uppercase">QUICK LINKS</span>
                    <div class="w-12 h-0.5 bg-gradient-to-r from-[#e8a838] to-[#f59e0b] mt-1.5 rounded-full"></div>
                </div>
                <div class="flex flex-col gap-2.5">
                    <a href="{{ route('home') }}" class="flex items-center justify-between bg-[#091724] border border-white/10 rounded-xl px-4 py-3 text-sm font-semibold text-white hover:border-[#e8a838] transition">
                        <span>Home</span>
                        <span class="text-[#e8a838] font-bold text-base">›</span>
                    </a>
                    <a href="{{ route('courses.index') }}" class="flex items-center justify-between bg-[#091724] border border-white/10 rounded-xl px-4 py-3 text-sm font-semibold text-white hover:border-[#e8a838] transition">
                        <span>Courses & Library</span>
                        <span class="text-[#e8a838] font-bold text-base">›</span>
                    </a>
                    <a href="{{ route('plans.index') }}" class="flex items-center justify-between bg-[#091724] border border-white/10 rounded-xl px-4 py-3 text-sm font-semibold text-white hover:border-[#e8a838] transition">
                        <span>Membership Plans</span>
                        <span class="text-[#e8a838] font-bold text-base">›</span>
                    </a>
                    <a href="{{ route('blog.index') }}" class="flex items-center justify-between bg-[#091724] border border-white/10 rounded-xl px-4 py-3 text-sm font-semibold text-white hover:border-[#e8a838] transition">
                        <span>Journal</span>
                        <span class="text-[#e8a838] font-bold text-base">›</span>
                    </a>
                    <a href="{{ route('legal.terms') }}" class="flex items-center justify-between bg-[#091724] border border-white/10 rounded-xl px-4 py-3 text-sm font-semibold text-white hover:border-[#e8a838] transition">
                        <span>Terms & Conditions</span>
                        <span class="text-[#e8a838] font-bold text-base">›</span>
                    </a>
                    <a href="{{ route('legal.privacy') }}" class="flex items-center justify-between bg-[#091724] border border-white/10 rounded-xl px-4 py-3 text-sm font-semibold text-white hover:border-[#e8a838] transition">
                        <span>Privacy Policy</span>
                        <span class="text-[#e8a838] font-bold text-base">›</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- DESKTOP / LAPTOP VIEW -->
        <div class="hidden md:grid grid-cols-4 gap-8 pb-10">
            <div>
                <div class="inline-flex items-center justify-center bg-white rounded-xl px-5 py-2.5 shadow-lg mb-4">
                    <span class="text-lg font-bold tracking-wider text-[#091724]">FOURSOULS</span>
                </div>
                <p class="text-sm text-slate-400 leading-relaxed mb-4">
                    Yoga for a calmer mind, a healthier body, and a brighter you.
                </p>
                <div class="flex gap-2">
                    <a href="https://linkedin.com" target="_blank" class="w-8 h-8 rounded-full border border-white/20 bg-white/5 flex items-center justify-center text-xs font-bold text-slate-300 hover:bg-[#e8a838] hover:text-[#091724] transition">in</a>
                    <a href="https://x.com" target="_blank" class="w-8 h-8 rounded-full border border-white/20 bg-white/5 flex items-center justify-center text-xs font-bold text-slate-300 hover:bg-[#e8a838] hover:text-[#091724] transition">X</a>
                    <a href="https://facebook.com" target="_blank" class="w-8 h-8 rounded-full border border-white/20 bg-white/5 flex items-center justify-center text-xs font-bold text-slate-300 hover:bg-[#e8a838] hover:text-[#091724] transition">f</a>
                    <a href="https://instagram.com" target="_blank" class="w-8 h-8 rounded-full border border-white/20 bg-white/5 flex items-center justify-center text-xs font-bold text-slate-300 hover:bg-[#e8a838] hover:text-[#091724] transition">ig</a>
                    <a href="https://youtube.com" target="_blank" class="w-8 h-8 rounded-full border border-white/20 bg-white/5 flex items-center justify-center text-xs font-bold text-slate-300 hover:bg-[#e8a838] hover:text-[#091724] transition">yt</a>
                </div>
            </div>
            <div>
                <h4 class="text-xs font-bold uppercase tracking-wider text-[#e8a838] mb-4">Quick Links</h4>
                <ul class="space-y-2 text-sm text-slate-400">
                    <li><a href="{{ route('courses.index') }}" class="hover:text-white transition">Courses & Workshops</a></li>
                    <li><a href="{{ route('plans.index') }}" class="hover:text-white transition">Membership Plans</a></li>
                    <li><a href="{{ route('blog.index') }}" class="hover:text-white transition">Journal</a></li>
                </ul>
            </div>
            <div>
                <h4 class="text-xs font-bold uppercase tracking-wider text-[#e8a838] mb-4">Support & Legal</h4>
                <ul class="space-y-2 text-sm text-slate-400">
                    <li><a href="mailto:{{ $supportEmail }}" class="hover:text-white transition">Contact Support</a></li>
                    <li><a href="{{ route('legal.terms') }}" class="hover:text-white transition">Terms & Conditions</a></li>
                    <li><a href="{{ route('legal.privacy') }}" class="hover:text-white transition">Privacy Policy</a></li>
                </ul>
            </div>
            <div>
                <h4 class="text-xs font-bold uppercase tracking-wider text-[#e8a838] mb-4">Community</h4>
                <p class="text-sm text-slate-400 mb-3">Join our newsletter for weekly wellness wisdom and class schedules.</p>
                <div class="flex gap-2">
                    <input type="email" placeholder="Your email address" class="bg-white/5 border border-white/10 rounded-full px-3.5 py-1.5 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-[#e8a838] flex-1" />
                    <button type="button" class="bg-[#e8a838] text-[#091724] text-xs font-bold px-4 py-1.5 rounded-full hover:bg-[#f59e0b] transition">Join</button>
                </div>
            </div>
        </div>

        <div class="pt-6 border-t border-white/10 flex flex-col sm:flex-row items-center justify-between text-xs text-slate-500 gap-3">
            <p>&copy; {{ $year }} {{ $companyName }}. All rights reserved.</p>
            <p>Designed with care for mindful living ✦</p>
        </div>
    </div>
</footer>