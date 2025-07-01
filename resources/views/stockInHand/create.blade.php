@extends('app')
@push('styles')
    <style>
        .row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .column {
            width: 50%;
            padding: 10px;
            box-sizing: border-box;
        }
    </style>
@endpush
@section('content')
    <div class="content">
        <h2 class="intro-y text-lg font-medium mt-10 heading">
            Add Profit and Loose
        </h2>
        <form action="{{ route('stock-in-hand.store') }}" method="POST" class="form-updated validate-form">
            @csrf
            <div class="row">
                <div class="column">
                    <!-- Select Product -->
                    <div class="input-form col-span-3 mt-3">
                        <label for="product" class="form-label w-full flex flex-col sm:flex-row">
                            Select Product<span style="color: red;margin-left: 3px;"> *</span>
                        </label>
                        <select id="product" name="product" class="form-control field-new" required>
                            <option value="" selected>Select Profit/Loose...</option>
                            @foreach ($products as $product)
                                <option value="{{ $product->id }}">{{ $product->product_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Price -->
                    <div class="input-form col-span-3 mt-3">
                        <label for="price" class="form-label w-full flex flex-col sm:flex-row">
                            Price
                        </label>
                        <input id="price" type="text" name="price" class="form-control field-new" maxlength="255">
                    </div>

                    <!-- Qty In Hand -->
                    <div class="input-form col-span-3 mt-3">
                        <label for="qty_in_hand" class="form-label w-full flex flex-col sm:flex-row">
                            Qty In Hand
                        </label>
                        <input id="qty_in_hand" type="text" name="qty_in_hand" class="form-control field-new"
                            maxlength="255">
                    </div>

                    <!-- Qty In Sold -->
                    <div class="input-form col-span-3 mt-3">
                        <label for="qty_in_sold" class="form-label w-full flex flex-col sm:flex-row">
                            Qty In Sold
                        </label>
                        <input id="qty_in_sold" type="text" name="qty_in_sold" class="form-control field-new"
                            maxlength="255">
                    </div>

                    <!-- Inventory Value -->
                    <div class="input-form col-span-3 mt-3">
                        <label for="inventory_value" class="form-label w-full flex flex-col sm:flex-row">
                            Inventory Value
                        </label>
                        <input id="inventory_value" type="text" name="inventory_value" class="form-control field-new"
                            maxlength="255">
                    </div>

                    <!-- Sale Value -->
                    <div class="input-form col-span-3 mt-3">
                        <label for="sale_value" class="form-label w-full flex flex-col sm:flex-row">
                            Sale Value
                        </label>
                        <input id="sale_value" type="text" name="sale_value" class="form-control field-new"
                            maxlength="255">
                    </div>

                    <!-- Available Stock -->
                    <div class="input-form col-span-3 mt-3">
                        <label for="available_stock" class="form-label w-full flex flex-col sm:flex-row">
                            Available Stock
                        </label>
                        <input id="available_stock" type="text" name="available_stock" class="form-control field-new"
                            maxlength="255">
                    </div>

            </div>
    </div>
    <a onclick="goBack()" class="btn btn-outline-primary shadow-md mr-2">Back</a>
    <button type="submit" class="btn btn-primary mt-5 btn-hover">Submit</button>
    </form>
    <!-- END: Validation Form -->
    </div>
@endsection
