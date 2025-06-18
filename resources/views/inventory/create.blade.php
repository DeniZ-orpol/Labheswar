@extends('app')

@section('content')
    <div class="content">
        <h2 class="intro-y text-lg font-medium mt-10 heading">
            Add Open Stock
        </h2>

        <form action="{{ route('inventory.store') }}" method="POST" class="form-updated validate-form mt-5">
            @csrf

            <div class="row">
                <!-- Select Product -->
                <div class="input-form col-span-3 mt-3">
                    <label for="product_id" class="form-label w-full flex flex-col sm:flex-row">
                        Product<span style="color: red;margin-left: 3px;"> *</span>
                    </label>
                    <select id="product_id" name="product_id" class="form-control field-new" required>
                        <option value="">Choose Product</option>
                        @foreach ($products as $product)
                            <option value="{{ $product->id }}">{{ $product->product_name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Quantity -->
                <div class="input-form col-span-3 mt-3">    
                    <label for="quantity" class="form-label w-full flex flex-col sm:flex-row">
                        Quantity<span style="color: red;margin-left: 3px;"> *</span>
                    </label>
                    <input id="quantity" type="number" name="quantity" class="form-control field-new" required>
                </div>

                <!-- Type -->
                <div class="input-form col-span-3 mt-3">
                    <label for="type" class="form-label w-full flex flex-col sm:flex-row">
                        Type<span style="color: red;margin-left: 3px;"> *</span>
                    </label>
                    <select id="type" name="type" class="form-control field-new" required>
                        <option value="in">IN</option>
                        <option value="out">OUT</option>
                    </select>
                </div>

                <!-- Reason -->
                <div class="input-form col-span-3 mt-3">
                    <label for="reason" class="form-label w-full flex flex-col sm:flex-row">
                        Reason
                    </label>
                    <input id="reason" type="text" name="reason" class="form-control field-new">
                </div>
            </div>

            <div class="text-right mt-5">
                <button type="submit" class="btn btn-primary w-32">Submit</button>
            </div>
        </form>
    </div>
@endsection
