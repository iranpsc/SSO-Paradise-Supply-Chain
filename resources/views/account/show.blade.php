<x-layouts.app>
    <div class="flex justify-center">
        <div class="flex flex-col gap-7 w-full xl:w-1/2 2xl:w-[40%] mx-auto md:text-xl  dark:text-[#FFFFFF]">
            <div class="card-header">{{ __('Account Info') }}</div>
            <x-partials.alerts />

            <div class="flex flex-col gap-5 md:flex-row items-center justify-between">
                <label for="name" class="form-label">{{ __('Name') }}</label>
                <input id="name" type="text"
                    class="bg-[#FCFCFC] dark:bg-[#000000] border-[#DEDEE9] dark:border-[#1A1A18] dark:text-[#FFFFFF] placeholder:text-[#868B90]  border-2 rounded-xl py-[10px] focus:border-[#84858F] focus:ring-0 text-[#868B90] font-normal focus:text-[#1A1A18] focus:border-[1px] w-full md:w-[70%]"
                    name="name" value="{{ Auth::user()->name }}" disabled>
            </div>

            <div class="flex flex-col gap-5 md:flex-row items-center justify-between">
                <label for="email" class="form-label">{{ __('E-Mail Address') }}</label>
                <input id="email" type="email"
                    class="bg-[#FCFCFC] dark:bg-[#000000] border-[#DEDEE9] dark:border-[#1A1A18] dark:text-[#FFFFFF] placeholder:text-[#868B90]  border-2 rounded-xl py-[10px] focus:border-[#84858F] focus:ring-0 text-[#868B90] font-normal focus:text-[#1A1A18] focus:border-[1px] w-full md:w-[70%]"
                    name="email" value="{{ Auth::user()->email }}" disabled>
            </div>

            <div class="border-t border-[#DEDEE9] dark:border-[#1A1A18] pt-5">
                <div class="card-header mb-5">{{ __('Wallet Connection') }}</div>

                @if (Auth::user()->wallet_address)
                    <div class="flex flex-col gap-5 md:flex-row items-center justify-between">
                        <label class="form-label">{{ __('Status') }}</label>
                        <span
                            class="inline-flex items-center gap-2 rounded-xl bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300 px-4 py-2 text-sm font-medium w-full md:w-[70%]">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                            {{ __('Connected') }}
                        </span>
                    </div>
                    <div class="flex flex-col gap-5 md:flex-row items-center justify-between mt-5">
                        <label for="wallet_address" class="form-label">{{ __('Wallet Address') }}</label>
                        <input id="wallet_address" type="text"
                            class="bg-[#FCFCFC] dark:bg-[#000000] border-[#DEDEE9] dark:border-[#1A1A18] dark:text-[#FFFFFF] placeholder:text-[#868B90] border-2 rounded-xl py-[10px] focus:border-[#84858F] focus:ring-0 text-[#868B90] font-normal focus:text-[#1A1A18] focus:border-[1px] w-full md:w-[70%] font-mono text-sm"
                            value="{{ Auth::user()->wallet_address }}" disabled>
                    </div>
                @else
                    <div class="flex flex-col gap-5 md:flex-row items-center justify-between">
                        <label class="form-label">{{ __('Status') }}</label>
                        <span
                            class="inline-flex items-center gap-2 rounded-xl bg-gray-100 dark:bg-[#1A1A18] text-[#868B90] px-4 py-2 text-sm font-medium w-full md:w-[70%]">
                            {{ __('Not Connected') }}
                        </span>
                    </div>
                    <div class="flex items-center justify-center mt-5">
                        <button type="button" id="connect-wallet-btn" onclick="connectWallet()"
                            class="w-full md:w-max bg-white dark:bg-slate-800 hover:bg-gray-100 dark:hover:bg-slate-700 border border-gray-200 dark:border-slate-700 rounded-xl px-6 py-3 flex items-center justify-center gap-2 transition focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed">
                            <svg id="wallet-icon" xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-yellow-500"
                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path d="M21 7v10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h14a2 2 0 012 2z" />
                                <circle cx="12" cy="12" r="4" fill="#f59e42" />
                            </svg>
                            <svg id="wallet-spinner"
                                class="hidden animate-spin h-5 w-5 text-gray-800 dark:text-gray-100"
                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            <span id="wallet-text" class="text-sm font-medium text-gray-800 dark:text-gray-100">
                                {{ __('Connect MetaMask') }}
                            </span>
                        </button>
                    </div>
                @endif
            </div>

            <div class="flex items-center justify-center">
                <a href="{{ route('account.edit') }}"
                    class="text-white bg-primery-blue dark:bg-dark-yellow py-[14px] px-[40px] mx-auto rounded-xl w-full md:w-max">{{ __('Update Account') }}</a>
            </div>
        </div>
    </div>

    @if (!Auth::user()->wallet_address)
        @push('scripts')
            <script>
                async function connectWallet() {
                    const btn = document.getElementById('connect-wallet-btn');
                    const icon = document.getElementById('wallet-icon');
                    const spinner = document.getElementById('wallet-spinner');
                    const text = document.getElementById('wallet-text');

                    if (!window.ethereum) {
                        alert("{{ __('Web3 wallet not found. Please install MetaMask or a compatible wallet.') }}");
                        return;
                    }

                    btn.disabled = true;
                    icon.classList.add('hidden');
                    spinner.classList.remove('hidden');
                    const originalText = text.innerText;
                    text.innerText = "{{ __('Connecting...') }}";

                    try {
                        const accounts = await window.ethereum.request({
                            method: 'eth_requestAccounts'
                        });
                        const address = accounts[0];

                        const nonceResponse = await fetch(`{{ route('web3.link.nonce') }}?address=${address}`);
                        if (!nonceResponse.ok) {
                            throw new Error("{{ __('Failed to retrieve authentication nonce.') }}");
                        }
                        const nonceData = await nonceResponse.json();

                        text.innerText = "{{ __('Signing message...') }}";

                        const msg = nonceData.nonce;
                        const msgHex = '0x' + Array.from(new TextEncoder().encode(msg))
                            .map(byte => byte.toString(16).padStart(2, '0'))
                            .join('');

                        const signature = await window.ethereum.request({
                            method: 'personal_sign',
                            params: [msgHex, address],
                        });

                        text.innerText = "{{ __('Verifying signature...') }}";

                        const linkResponse = await fetch('{{ route('web3.link') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({
                                address: address,
                                signature: signature
                            })
                        });

                        const linkData = await linkResponse.json();

                        if (linkResponse.ok) {
                            text.innerText = "{{ __('Wallet connected successfully.') }}";
                            window.location.reload();
                        } else {
                            throw new Error(linkData.message || "{{ __('Signature verification failed.') }}");
                        }
                    } catch (error) {
                        console.error("Web3 link error:", error);

                        let errorMsg = "{{ __('Something went wrong. Please try again.') }}";
                        if (error.code === 4001) {
                            errorMsg = "{{ __('Connection or signature request was rejected.') }}";
                        } else if (error.message) {
                            errorMsg = error.message;
                        }
                        alert(errorMsg);

                        btn.disabled = false;
                        icon.classList.remove('hidden');
                        spinner.classList.add('hidden');
                        text.innerText = originalText;
                    }
                }
            </script>
        @endpush
    @endif
</x-layouts.app>
