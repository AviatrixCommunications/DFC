export default function initBlogFilter() {
    const resultsContainer = document.getElementById('blog-posts-results');
    const catButtons = document.querySelectorAll('.blog-listing__cat-btn');
    const yearSelect = document.getElementById('blog-year-filter');
    const statusRegion = document.getElementById('blog-listing-status');

    if (!resultsContainer) return;

    let currentPage = 1;
    let maxPages =
        parseInt(
            resultsContainer.querySelector('.blog-listing__load-more')?.dataset.maxPages
        ) || 1;
    let isLoading = false;

    function getFilters() {
        const activeBtn = document.querySelector('.blog-listing__cat-btn.is-active');
        return {
            category: activeBtn?.dataset.category || '',
            year: yearSelect?.value || '',
        };
    }

    function announceStatus(message) {
        if (statusRegion) {
            statusRegion.textContent = message;
        }
    }

    function fetchPosts(page, append) {
        if (isLoading) return;
        isLoading = true;

        const filters = getFilters();
        const action = append ? 'load_more_blog_posts' : 'filter_blog_posts';

        if (!append) {
            resultsContainer.setAttribute('aria-busy', 'true');
            resultsContainer.classList.add('blog-listing__results--loading');
            announceStatus('Loading posts...');
        }

        const loadMoreBtn = resultsContainer.querySelector('.blog-listing__load-more');
        if (append && loadMoreBtn) {
            loadMoreBtn.disabled = true;
            loadMoreBtn.textContent = 'Loading...';
            announceStatus('Loading more posts...');
        }

        fetch(blogFilter.ajaxurl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({
                action,
                nonce: blogFilter.nonce,
                category: filters.category,
                year: filters.year,
                page,
            }),
        })
            .then((res) => res.json())
            .then((data) => {
                if (!data.success) return;

                maxPages = data.data.max_pages;

                if (append) {
                    const list = resultsContainer.querySelector('.blog-listing__list');
                    if (list) {
                        const temp = document.createElement('div');
                        temp.innerHTML = data.data.html;
                        const newItems = temp.querySelectorAll('.post-item');
                        const firstNew = newItems[0] || null;

                        while (temp.firstChild) {
                            list.appendChild(temp.firstChild);
                        }

                        // Move focus to first new item for keyboard/SR users
                        if (firstNew) {
                            firstNew.setAttribute('tabindex', '-1');
                            firstNew.focus({ preventScroll: false });
                        }

                        announceStatus(newItems.length + ' more posts loaded.');
                    }
                } else {
                    const wrapper = resultsContainer.querySelector(
                        '.blog-listing__load-more-wrapper'
                    );
                    if (wrapper) wrapper.remove();

                    const list = resultsContainer.querySelector('.blog-listing__list');
                    if (list) {
                        list.innerHTML = data.data.html;
                    } else {
                        resultsContainer.innerHTML =
                            '<div class="blog-listing__list">' + data.data.html + '</div>';
                    }

                    // Announce result count
                    const count = resultsContainer.querySelectorAll('.post-item').length;
                    if (count > 0) {
                        announceStatus(count + ' posts found.');
                    } else {
                        announceStatus('No posts found.');
                    }
                }

                // Toggle load-more button
                const existingBtn = resultsContainer.querySelector(
                    '.blog-listing__load-more-wrapper'
                );
                if (existingBtn) existingBtn.remove();

                if (currentPage < maxPages) {
                    const btnWrapper = document.createElement('div');
                    btnWrapper.className = 'blog-listing__load-more-wrapper';
                    btnWrapper.innerHTML =
                        '<button type="button" class="blog-listing__load-more" data-max-pages="' +
                        maxPages +
                        '">View More Articles</button>';
                    resultsContainer.appendChild(btnWrapper);
                }
            })
            .catch((err) => {
                console.error('Blog filter error:', err);
                announceStatus('An error occurred loading posts.');
            })
            .finally(() => {
                isLoading = false;
                resultsContainer.classList.remove('blog-listing__results--loading');
                resultsContainer.setAttribute('aria-busy', 'false');
            });
    }

    // Category button clicks
    catButtons.forEach((btn) => {
        btn.addEventListener('click', () => {
            catButtons.forEach((b) => {
                b.classList.remove('is-active');
                b.setAttribute('aria-pressed', 'false');
            });
            btn.classList.add('is-active');
            btn.setAttribute('aria-pressed', 'true');
            currentPage = 1;
            fetchPosts(1, false);
        });
    });

    // Year dropdown
    if (yearSelect) {
        yearSelect.addEventListener('change', () => {
            currentPage = 1;
            fetchPosts(1, false);
        });
    }

    // Load more (event delegation — button is re-created after AJAX)
    resultsContainer.addEventListener('click', (e) => {
        if (e.target.closest('.blog-listing__load-more')) {
            currentPage++;
            fetchPosts(currentPage, true);
        }
    });
}
