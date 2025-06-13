#!/bin/bash

echo "🚀 Creating ERP Branch Migrations..."

# Create branch migrations directory
echo "📁 Creating branch migrations directory..."
mkdir -p database/migrations/branch

# Array of migration names
migrations=(
    "create_item_master_table"
    # "create_customer_categories_table"
    # "create_customers_table"
    # "create_product_categories_table"
    # "create_units_table"
    # "create_products_table"
    # "create_inventory_stock_table"
    # "create_stock_movements_table"
    # "create_sales_orders_table"
    # "create_sales_order_items_table"
    # "create_sales_invoices_table"
    # "create_workflow_templates_table"
    # "create_workflow_instances_table"
    # "create_workflow_step_executions_table"
    # "create_document_sequences_table"
)

# Create each migration
for migration in "${migrations[@]}"
do
    echo "📄 Creating migration: $migration"
    php artisan make:migration $migration --path=database/migrations/branch
done

echo ""
echo "✅ All branch migration files created successfully!"
echo ""
echo "📝 NEXT STEPS:"
echo "1. Copy migration content from the provided code into each file"
echo "2. Update your .env file with database credentials"
echo "3. Create config/branches.php file"
echo "4. Run: php artisan erp:setup-2branches"
echo ""
echo "📋 FILES CREATED:"
for migration in "${migrations[@]}"
do
    echo "   - database/migrations/branch/*_$migration.php"
done