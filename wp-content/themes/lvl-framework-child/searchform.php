<form role="search" method="get" class="search-form" action="<?php echo esc_url(home_url('/')); ?>">
    <div class="input-group">
        <input type="search" class="search-input form-control" name="s" value="<?php echo get_search_query(); ?>" placeholder="<?php _e('Search...'); ?>" aria-label="Search" aria-describedby="search-btn" required>
        <button type="submit" class="btn btn-secondary" id="search-btn" aria-label="Perform search.">
            <svg xmlns="http://www.w3.org/2000/svg" width="17" height="18" viewBox="0 0 21 22" fill="none">
                <defs><title id="search-icon-title-2">Perform search.</title></defs>
                <circle cx="12" cy="9" r="7" stroke="currentColor" stroke-width="3"/>
                <path d="M7 15L2.5 19.5" stroke="currentColor" stroke-width="3" stroke-linecap="square"/>
            </svg>
        </button>
    </div>
</form>