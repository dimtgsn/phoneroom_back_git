<nav class="mt-2">
    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu"
        data-accordion="false">

        <li class="nav-header">ADMIN PANEL</li>
        <li class="nav-item">
            <a href="{{ route('admin.products.index') }}" class="{{ Request::is('*/products*') ? 'active' : '' }} nav-link">
                <i class="nav-icon fa fa-solid fa-store"></i>
                <p>
                    Товары
                </p>
            </a>
        </li>


        <li class="nav-item">
            <a href="{{ route('admin.orders.index') }}" class="{{ Request::is('*/orders*') ? 'active' : '' }} nav-link">
                <i class="nav-icon fa fa-duotone fa-money-bill"></i>
                <p>
                    Заказы
                </p>
            </a>
        </li>

        <li class="nav-item">
            <a href="{{ route('admin.tags.index') }}" class="{{ Request::is('*/tags*') ? 'active' : '' }} nav-link">
                <i class="nav-icon fa fa-solid fa-hashtag"></i>
                <p>
                    Теги
                </p>
            </a>
        </li>

        <li class="nav-item">
            <a href="{{ route('admin.categories.index') }}" class="{{ Request::is('*/categories*') ? 'active' : '' }} nav-link">
                <i class="nav-icon fa fa-list"></i>
                <p>
                    Категории
                </p>
            </a>
        </li>

        <li class="nav-item">
            <a href="{{ route('admin.brands.index') }}" class="{{ Request::is('*/brands*') ? 'active' : '' }} nav-link">
                <i class="nav-icon fa fa-solid fa-copyright"></i>
                <p>
                    Бренды
                </p>
            </a>
        </li>
        <li class="nav-item menu-is-opening menu-open">
            <a href="#" class="nav-link">
                <i class="nav-icon fa fa-solid fa-image"></i>
                <p>
                    Изображения
                    <i class="right fas fa-angle-left"></i>
                </p>
            </a>
            <ul class="nav nav-treeview" style="display: block; height: 129.6px; padding-top: 0; margin-top: 0; padding-bottom: 0; margin-bottom: 0;">
                <li class="nav-item">
                    <a href="{{ route('admin.main_images.index') }}" class="nav-link {{ Request::is('*/images/main*') ? 'active' : '' }}">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Главная</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.banner_images.index') }}" class="nav-link {{ Request::is('*/images/banner*') ? 'active' : '' }}">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Банеры</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.promotion_images.index') }}" class="nav-link {{ Request::is('*/images/promotion*') ? 'active' : '' }}">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Акции</p>
                    </a>
                </li>
            </ul>
        </li>
    </ul>
</nav>