@extends('layouts.app')

@section('page-title', 'Menu Items')

@section('content')
@if(session('success'))
    <div class="auth-alert success" style="margin-bottom: 20px;">{{ session('success') }}</div>
@endif

@if($errors->any())
    <div class="auth-alert error" style="margin-bottom: 20px;">{{ $errors->first() }}</div>
@endif

<!-- Add Menu Item Form -->
<div class="table-card" style="margin-bottom: 24px;">
    <div class="top-table">
        <h3>Add New Menu Offering</h3>
    </div>
    <form action="{{ route('menu.store') }}" method="POST">
        @csrf
        <div style="display: grid; grid-template-columns: 2fr 1.5fr 1fr; gap: 14px; margin-bottom: 14px;">
            <div class="form-group" style="margin-bottom: 0;">
                <label>Dish / Drink Name</label>
                <input type="text" name="item_name" class="form-input" placeholder="e.g. Longsilog" required />
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label>Category</label>
                <input type="text" name="category" list="category-options" class="form-input"
                       placeholder="Pick existing or type new" required autocomplete="off" />
                <datalist id="category-options">
                    @foreach($categories as $cat)
                        <option value="{{ $cat }}"></option>
                    @endforeach
                </datalist>
                <div style="font-size: 11px; color: var(--text-muted); margin-top: 4px;">
                    System categories: <strong>Breakfast Set Meal</strong>, <strong>Hot Drinks</strong>, <strong>Cold Drinks</strong>, <strong>Rice</strong>, <strong>Food Add-ons</strong>, <strong>Snacks</strong>
                </div>
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label>Price (₱)</label>
                <input type="number" step="0.01" name="unit_price" class="form-input" placeholder="150.00" required />
            </div>
        </div>

        <!-- Recipe Builder -->
        <div style="background: rgba(46,34,24,0.6); border: 1px solid var(--accent-gold-border); border-radius: 10px; padding: 16px; margin-bottom: 14px;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 12px;">
                <h4 style="color: var(--accent-gold-primary); font-size: 14px; margin:0;">
                    <i class="fa-solid fa-flask"></i> Recipe (Ingredients per serving)
                </h4>
                <button type="button" onclick="addIngredient()" class="btn-portal" style="padding: 5px 12px; font-size: 12px;">+ Add Ingredient</button>
            </div>
            <p style="font-size:12px; color: var(--text-muted); margin-bottom: 10px;">
                Leave empty if this item does not consume inventory (e.g., bundled package).
                <strong>Units must match inventory units (g, ml, pcs).</strong>
            </p>
            <div id="ingredients_container"></div>
        </div>

        <div style="margin-top: 14px; text-align: right;">
            <button type="submit" class="btn-submit" style="width: auto; padding: 10px 24px;">Save Menu Item</button>
        </div>
    </form>
</div>

<!-- Menu Catalog -->
<div class="table-card">
    <div class="top-table">
        <h3>Menu Catalog</h3>
        <div style="display:flex; gap:10px; align-items:center;">
            <label style="font-size: 13px; color: var(--text-muted);">Filter:</label>
            <select id="category-filter" class="form-input select-dark" style="margin:0; padding: 6px 12px; min-width: 200px;" onchange="filterMenu()">
                <option value="">All Categories ({{ $menuItems->count() }})</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat }}">{{ $cat }} ({{ $menuItems->where('category', $cat)->count() }})</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Dish / Drink Name</th>
                    <th>Category</th>
                    <th>Selling Price</th>
                    <th>Recipe / Ingredients</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="menu-table-body">
                @forelse($menuItems as $item)
                <tr data-category="{{ $item->category }}">
                    <td>MNU-{{ str_pad($item->id, 3, '0', STR_PAD_LEFT) }}</td>
                    <td><strong>{{ $item->item_name }}</strong></td>
                    <td><span class="room-tag">{{ $item->category }}</span></td>
                    <td>₱ {{ number_format($item->unit_price, 2) }}</td>
                    <td style="text-align:left; font-size:12px;">
                        @if($item->ingredients->isEmpty())
                            <span style="color: var(--text-muted);">No inventory impact</span>
                        @else
                            @foreach($item->ingredients as $ing)
                                <div>
                                    {{ number_format($ing->quantity_per_serving, 2) }} {{ $ing->inventoryItem->unit_of_measure ?? '' }}
                                    — {{ $ing->inventoryItem->item_name ?? 'Unknown' }}
                                </div>
                            @endforeach
                        @endif
                    </td>
                    <td>
                        <button type="button"
                                class="btn-portal edit-item-btn"
                                style="padding: 4px 10px; font-size: 11px;"
                                data-id="{{ $item->id }}"
                                data-name="{{ $item->item_name }}"
                                data-category="{{ $item->category }}"
                                data-price="{{ (float) $item->unit_price }}"
                                data-ingredients="{{ $item->ingredients->map(fn($i) => ['inventory_item_id' => $i->inventory_item_id, 'quantity_per_serving' => (float) $i->quantity_per_serving])->toJson() }}">
                            <i class="fa-solid fa-pen"></i> Edit
                        </button>
                    </td>
                </tr>
                @empty
                <tr id="empty-row">
                    <td colspan="6" style="text-align:center; padding: 24px; color: var(--text-muted);">
                        No menu items yet. Add your first offering above.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Edit Menu Item Modal -->
<div id="edit-menu-modal" style="
    display: none;
    position: fixed;
    top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(0, 0, 0, 0.7);
    z-index: 9999;
    align-items: center;
    justify-content: center;
    padding: 20px;
    overflow-y: auto;
" onclick="if (event.target.id === 'edit-menu-modal') closeEditModal();">

    <div class="table-card" style="max-width: 800px; width: 100%; margin: 0; position: relative; max-height: 90vh; overflow-y: auto;">
        <button type="button" onclick="closeEditModal()" style="
            position: absolute; top: 12px; right: 12px;
            background: transparent; border: none;
            color: var(--accent-gold-primary); font-size: 22px;
            cursor: pointer; z-index: 1;
        "><i class="fa-solid fa-xmark"></i></button>

        <div class="top-table" style="padding-right: 40px;">
            <h3><i class="fa-solid fa-pen"></i> Edit Menu Item</h3>
        </div>

        <form id="edit-menu-form" method="POST">
            @csrf
            @method('PUT')

            <div style="display: grid; grid-template-columns: 2fr 1.5fr 1fr; gap: 14px; margin-bottom: 14px;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label>Dish / Drink Name</label>
                    <input type="text" name="item_name" id="edit-item-name" class="form-input" required />
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label>Category</label>
                    <input type="text" name="category" id="edit-category" list="category-options" class="form-input" required autocomplete="off" />
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label>Price (₱)</label>
                    <input type="number" step="0.01" name="unit_price" id="edit-price" class="form-input" required />
                </div>
            </div>

            <div style="background: rgba(46,34,24,0.6); border: 1px solid var(--accent-gold-border); border-radius: 10px; padding: 16px; margin-bottom: 14px;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 12px;">
                    <h4 style="color: var(--accent-gold-primary); font-size: 14px; margin:0;">
                        <i class="fa-solid fa-flask"></i> Recipe (Ingredients per serving)
                    </h4>
                    <button type="button" onclick="addEditIngredient()" class="btn-portal" style="padding: 5px 12px; font-size: 12px;">+ Add Ingredient</button>
                </div>
                <div id="edit-ingredients-container"></div>
            </div>

            <div style="text-align: right; display: flex; gap: 10px; justify-content: flex-end;">
                <button type="button" onclick="closeEditModal()" class="btn-portal" style="padding: 10px 24px;">Cancel</button>
                <button type="submit" class="btn-submit" style="width: auto; padding: 10px 24px;">Save Changes</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<meta name="inventory-items-list" content="{{ $inventoryItems->map(fn($i) => ['id' => $i->id, 'name' => $i->item_name, 'unit' => $i->unit_of_measure])->toJson() }}">
<script>
const INV_ITEMS = JSON.parse(document.querySelector('meta[name="inventory-items-list"]').content);

// ---------- Add Form ----------
let ingIdx = 0;
function addIngredient() {
    const container = document.getElementById('ingredients_container');
    const idx = ingIdx++;
    container.appendChild(buildIngredientRow(idx, 'ingredients', null, null));
}

function buildIngredientRow(idx, prefix, selectedId, selectedQty) {
    const div = document.createElement('div');
    div.id = `${prefix}_row_${idx}`;
    div.style.cssText = 'display:grid; grid-template-columns: 3fr 1fr 1fr auto; gap:8px; align-items:center; margin-bottom:8px;';

    const opts = INV_ITEMS.map(i => {
        const sel = (selectedId && String(i.id) === String(selectedId)) ? 'selected' : '';
        return `<option value="${i.id}" ${sel}>${i.name} (${i.unit})</option>`;
    }).join('');

    const unitLabel = selectedId
        ? (INV_ITEMS.find(i => String(i.id) === String(selectedId))?.unit || 'unit')
        : 'unit';

    div.innerHTML = `
        <select name="${prefix}[${idx}][inventory_item_id]" class="form-input select-dark" style="margin-bottom:0;" required onchange="updateUnitLabel(this, '${prefix}_unit_${idx}')">
            <option value="">-- Pick ingredient --</option>
            ${opts}
        </select>
        <input type="number" step="0.001" name="${prefix}[${idx}][quantity_per_serving]" class="form-input" placeholder="Qty" min="0.001" value="${selectedQty ?? ''}" required style="margin-bottom:0;" />
        <span id="${prefix}_unit_${idx}" style="font-size: 12px; color: var(--text-muted); text-align:center;">${unitLabel}</span>
        <button type="button" onclick="document.getElementById('${prefix}_row_${idx}').remove()" style="background:#ef4444;border:none;color:#fff;border-radius:6px;padding:6px 10px;cursor:pointer;">
            <i class="fa-solid fa-times"></i>
        </button>`;

    if (selectedId) {
        div.querySelector('select').value = selectedId;
    }

    return div;
}

function updateUnitLabel(sel, targetId) {
    const opt = INV_ITEMS.find(i => String(i.id) === String(sel.value));
    document.getElementById(targetId).innerText = opt ? opt.unit : 'unit';
}

function filterMenu() {
    const selected = document.getElementById('category-filter').value;
    const rows     = document.querySelectorAll('#menu-table-body tr[data-category]');
    const emptyRow = document.getElementById('empty-row');
    let visible    = 0;

    rows.forEach(row => {
        if (selected === '' || row.dataset.category === selected) {
            row.style.display = '';
            visible++;
        } else {
            row.style.display = 'none';
        }
    });

    if (emptyRow) {
        emptyRow.style.display = visible === 0 ? '' : 'none';
    }
}

// ---------- Edit Modal ----------
let editIngIdx = 0;

function openEditModal(id, name, category, price, ingredients) {
    document.getElementById('edit-menu-form').action = `/menu-items/${id}`;
    document.getElementById('edit-item-name').value = name;
    document.getElementById('edit-category').value = category;
    document.getElementById('edit-price').value = price;

    const container = document.getElementById('edit-ingredients-container');
    container.innerHTML = '';
    editIngIdx = 0;

    if (ingredients && ingredients.length > 0) {
        ingredients.forEach(ing => {
            const idx = editIngIdx++;
            container.appendChild(buildIngredientRow(idx, 'ingredients', ing.inventory_item_id, ing.quantity_per_serving));
        });
    }

    document.getElementById('edit-menu-modal').style.display = 'flex';
}

function addEditIngredient() {
    const container = document.getElementById('edit-ingredients-container');
    const idx = editIngIdx++;
    container.appendChild(buildIngredientRow(idx, 'ingredients', null, null));
}

function closeEditModal() {
    document.getElementById('edit-menu-modal').style.display = 'none';
}

// ---------- Bootstrap ----------
document.addEventListener('DOMContentLoaded', function () {
    // Bind edit buttons
    document.querySelectorAll('.edit-item-btn').forEach(button => {
        button.addEventListener('click', function () {
            const id          = this.dataset.id;
            const name        = this.dataset.name;
            const category    = this.dataset.category;
            const price       = parseFloat(this.dataset.price);
            const ingredients = JSON.parse(this.dataset.ingredients);

            openEditModal(id, name, category, price, ingredients);
        });
    });

    // ESC to close modal
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeEditModal();
    });
});
</script>
@endsection