@extends('app')
@section('content')
    <!-- BEGIN: Content -->
    <div class="content">
        <h2 class="intro-y text-lg font-medium mt-10 heading">
            Ledger
        </h2>
        <div class="grid grid-cols-12 gap-6 mt-5 grid-updated">
            <!-- BEGIN: Ledger Layout -->
            <!-- DataTable: Add class 'datatable' to your table -->
            <div class="col-span-12 mt-6">
                <div class="intro-y mt-8 overflow-auto sm:mt-0 lg:overflow-visible">
                    <div class="space-y-2">
                        <!-- SUNDRY DEBTORS -->
                        <div class="w-full bg-primary border border-white text-white text-center py-4 px-6 rounded-lg transition-colors cursor-pointer">
                            <a href="{{ route('ledger.index', ['type' => 'SUNDRY DEBTORS']) }}" class="block text-white font-medium">
                            {{-- <a href="#" class="block text-white font-medium"> --}}
                                SUNDRY DEBTORS
                            </a>
                        </div>

                        <!-- SUNDRY DEBTORS (E-COMMERCE) -->
                        <div class="w-full bg-primary border border-white text-white text-center py-4 px-6 rounded-lg transition-colors cursor-pointer">
                            <a href="{{ route('ledger.index', ['type' => 'SUNDRY DEBTORS (E-COMMERCE)']) }}" class="block text-white font-medium">
                            {{-- <a href="#" class="block text-white font-medium"> --}}
                                SUNDRY DEBTORS (E-COMMERCE)
                            </a>
                        </div>

                        <!-- SUNDRY DEBTORS (FIELD STAFF) -->
                        <div class="w-full bg-primary border border-white text-white text-center py-4 px-6 rounded-lg transition-colors cursor-pointer">
                            <a href="{{ route('ledger.index', ['type' => 'SUNDRY DEBTORS (FIELD STAFF)']) }}" class="block text-white font-medium">
                            {{-- <a href="#" class="block text-white font-medium"> --}}
                                SUNDRY DEBTORS (FIELD STAFF)
                            </a>
                        </div>

                        <!-- SUNDRY CREDITORS -->
                        <div class="w-full bg-primary border border-white text-white text-center py-4 px-6 rounded-lg transition-colors cursor-pointer">
                            <a href="{{ route('ledger.index', ['type' => 'SUNDRY CREDITORS']) }}" class="block text-white font-medium">
                            {{-- <a href="#" class="block text-white font-medium"> --}}
                                SUNDRY CREDITORS
                            </a>
                        </div>

                        <!-- SUNDRY CREDITORS (E-COMMERCE) -->
                        <div class="w-full bg-primary border border-white text-white text-center py-4 px-6 rounded-lg transition-colors cursor-pointer">
                            <a href="{{ route('ledger.index', ['type' => 'SUNDRY CREDITORS (E-COMMERCE)']) }}" class="block text-white font-medium">
                            {{-- <a href="#" class="block text-white font-medium"> --}}
                                SUNDRY CREDITORS (E-COMMERCE)
                            </a>
                        </div>

                        <!-- SUNDRY CREDITORS (EXPENSES PAYABLE) -->
                        <div class="w-full bg-primary border border-white text-white text-center py-4 px-6 rounded-lg transition-colors cursor-pointer">
                            <a href="{{ route('ledger.index', ['type' => 'SUNDRY CREDITORS (EXPENSES PAYABLE)']) }}" class="block text-white font-medium">
                            {{-- <a href="#" class="block text-white font-medium"> --}}
                                SUNDRY CREDITORS (EXPENSES PAYABLE)
                            </a>
                        </div>

                        <!-- SUNDRY CREDITORS (FIELD STAFF) -->
                        <div class="w-full bg-primary border border-white text-white text-center py-4 px-6 rounded-lg transition-colors cursor-pointer">
                            <a href="{{ route('ledger.index', ['type' => 'SUNDRY CREDITORS (FIELD STAFF)']) }}" class="block text-white font-medium">
                            {{-- <a href="#" class="block text-white font-medium"> --}}
                                SUNDRY CREDITORS (FIELD STAFF)
                            </a>
                        </div>

                        <!-- SUNDRY CREDITORS (MANUFACTURERS) -->
                        <div class="w-full bg-primary border border-white text-white text-center py-4 px-6 rounded-lg transition-colors cursor-pointer">
                            <a href="{{ route('ledger.index', ['type' => 'SUNDRY CREDITORS (MANUFACTURERS)']) }}" class="block text-white font-medium">
                            {{-- <a href="#" class="block text-white font-medium"> --}}
                                SUNDRY CREDITORS (MANUFACTURERS)
                            </a>
                        </div>

                        <!-- SUNDRY CREDITORS (SUPPLIERS) -->
                        <div class="w-full bg-primary border border-white text-white text-center py-4 px-6 rounded-lg transition-colors cursor-pointer">
                            <a href="{{ route('ledger.index', ['type' => 'SUNDRY CREDITORS (SUPPLIERS)']) }}" class="block text-white font-medium">
                            {{-- <a href="#" class="block text-white font-medium"> --}}
                                SUNDRY CREDITORS (SUPPLIERS)
                            </a>
                        </div>

                        <!-- BANK -->
                        <div class="w-full bg-primary border border-white text-white text-center py-4 px-6 rounded-lg transition-colors cursor-pointer">
                            <a href="{{ route('bank.index') }}" class="block text-white font-medium">
                            {{-- <a href="#" class="block text-white font-medium"> --}}
                                BANK
                            </a>
                        </div>

                        <!-- Stock in Hand -->
                        <div class="w-full bg-primary border border-white text-white text-center py-4 px-6 rounded-lg transition-colors cursor-pointer">
                            <a href="{{ route('stock-in-hand.index') }}" class="block text-white font-medium">
                            {{-- <a href="#" class="block text-white font-medium"> --}}
                                Stock in Hand
                            </a>
                        </div>

                        <!-- Profit And Loss -->
                        <div class="w-full bg-primary border border-white text-white text-center py-4 px-6 rounded-lg transition-colors cursor-pointer">
                            <a href="{{ route('profit-loose.index') }}" class="block text-white font-medium">
                            {{-- <a href="#" class="block text-white font-medium"> --}}
                                Profit And Loss
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection