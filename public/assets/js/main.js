/**
 * =======================================================
 * Template Name: ModernAdmin - Bootstrap Admin Template
 * Template URL:  https://bootstrapmade.com/modern-admin-bootstrap-html-admin-template/
 * Updated: Jan 28, 2026
 * Author: BootstrapMade.com
 * License: https://bootstrapmade.com/license/
 * =======================================================
 */
/**
 * Main JavaScript - Core functionality
 * Handles sidebar, mobile menu, search, scroll to top, etc.
 */

(function () {
    "use strict";

    // DOM Ready
    document.addEventListener("DOMContentLoaded", function () {
        initSidebar();
        initSearch();
        initBackToTop();
        initDropdowns();
        initTooltips();
    });

    /**
     * Sidebar Toggle
     */
    function initSidebar() {
        const body = document.body;
        const sidebarToggle = document.querySelector(".sidebar-toggle");
        const sidebarClose = document.querySelector(".sidebar-close");
        const sidebarOverlay = document.querySelector(".sidebar-overlay");

        // Toggle sidebar on desktop (collapse/expand)
        if (sidebarToggle) {
            sidebarToggle.addEventListener("click", function (e) {
                e.preventDefault();

                if (window.innerWidth >= 1200) {
                    // Desktop: Toggle collapsed state
                    body.classList.toggle("sidebar-collapsed");
                    localStorage.setItem(
                        "sidebar-collapsed",
                        body.classList.contains("sidebar-collapsed"),
                    );
                } else {
                    // Mobile: Toggle open state
                    body.classList.toggle("sidebar-open");
                }
            });
        }

        // Close sidebar on mobile
        if (sidebarClose) {
            sidebarClose.addEventListener("click", function (e) {
                e.preventDefault();
                body.classList.remove("sidebar-open");
            });
        }

        // Close sidebar when clicking overlay
        if (sidebarOverlay) {
            sidebarOverlay.addEventListener("click", function () {
                body.classList.remove("sidebar-open");
            });
        }

        // Restore collapsed state from localStorage
        if (
            localStorage.getItem("sidebar-collapsed") === "true" &&
            window.innerWidth >= 1200
        ) {
            body.classList.add("sidebar-collapsed");
        }

        // Handle window resize
        let resizeTimer;
        window.addEventListener("resize", function () {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(function () {
                if (window.innerWidth >= 1200) {
                    // Desktop: close mobile sidebar if open
                    body.classList.remove("sidebar-open");
                    // Restore collapsed state from localStorage if user had it enabled
                    if (localStorage.getItem("sidebar-collapsed") === "true") {
                        body.classList.add("sidebar-collapsed");
                    }
                } else {
                    // Mobile: disable compact/collapsed sidebar (doesn't make sense on mobile)
                    body.classList.remove("sidebar-collapsed");
                }
            }, 250);
        });

        // Initialize sidebar navigation
        initSidebarNav();
    }

    /**
     * Sidebar Navigation - Handle submenus
     */
    function initSidebarNav() {
        // Handle both top-level (.nav-item.has-submenu) and nested (.has-submenu) submenus
        const navLinks = document.querySelectorAll(".has-submenu > .nav-link");

        navLinks.forEach(function (link) {
            link.addEventListener("click", function (e) {
                e.preventDefault();

                const parent = this.parentElement;
                const submenu = parent.querySelector(":scope > .nav-submenu");

                // If sidebar is collapsed on desktop, don't toggle
                if (
                    document.body.classList.contains("sidebar-collapsed") &&
                    window.innerWidth >= 1200
                ) {
                    return;
                }

                // Toggle this submenu
                const isOpen = parent.classList.contains("open");

                // Close other open submenus at the same level
                const siblings = parent.parentElement.querySelectorAll(
                    ":scope > .has-submenu.open",
                );
                siblings.forEach(function (sibling) {
                    if (sibling !== parent) {
                        closeSubmenu(sibling);
                    }
                });

                // Toggle current submenu
                if (isOpen) {
                    closeSubmenu(parent);
                } else {
                    openSubmenu(parent);
                }
            });
        });

        // Auto-expand active submenu on page load
        const activeItems = document.querySelectorAll(
            ".nav-submenu .nav-link.active",
        );
        activeItems.forEach(function (activeItem) {
            let parent = activeItem.closest(".has-submenu");
            while (parent) {
                openSubmenu(parent, false);
                parent = parent.parentElement.closest(".has-submenu");
            }
        });

        // Scroll to active nav item after submenus are expanded
        scrollToActiveNavItem();
    }

    /**
     * Scroll sidebar to center the active nav item if it's not visible
     */
    function scrollToActiveNavItem() {
        const sidebarNav = document.querySelector(".sidebar-nav");
        if (!sidebarNav) return;

        // Find the active nav link (could be top-level or inside a submenu)
        const activeLink = sidebarNav.querySelector(".nav-link.active");
        if (!activeLink) return;

        // Use requestAnimationFrame to ensure DOM has updated after submenu expansion
        requestAnimationFrame(function () {
            const sidebarRect = sidebarNav.getBoundingClientRect();
            const activeRect = activeLink.getBoundingClientRect();

            // Calculate the position of the active item relative to the sidebar
            const activeTop =
                activeRect.top - sidebarRect.top + sidebarNav.scrollTop;
            const activeBottom = activeTop + activeRect.height;

            // Check if the active item is fully visible
            const visibleTop = sidebarNav.scrollTop;
            const visibleBottom = visibleTop + sidebarNav.clientHeight;

            const isFullyVisible =
                activeTop >= visibleTop && activeBottom <= visibleBottom;

            if (!isFullyVisible) {
                // Calculate scroll position to center the active item
                const targetScrollTop =
                    activeTop -
                    sidebarNav.clientHeight / 2 +
                    activeRect.height / 2;

                // Clamp to valid scroll range
                const maxScroll =
                    sidebarNav.scrollHeight - sidebarNav.clientHeight;
                const clampedScrollTop = Math.max(
                    0,
                    Math.min(targetScrollTop, maxScroll),
                );

                // Smooth scroll to the active item
                sidebarNav.scrollTo({
                    top: clampedScrollTop,
                    behavior: "smooth",
                });
            }
        });
    }

    /**
     * Open a submenu
     */
    function openSubmenu(item, animate = true) {
        const link = item.querySelector(":scope > .nav-link");
        const submenu = item.querySelector(":scope > .nav-submenu");

        if (!submenu) return;

        item.classList.add("open");
        if (link) {
            link.setAttribute("aria-expanded", "true");
        }

        if (animate) {
            submenu.style.maxHeight = submenu.scrollHeight + "px";
            // Update parent submenu height after child has expanded
            requestAnimationFrame(function () {
                updateParentHeight(item);
            });
        } else {
            submenu.style.maxHeight = "none";
            updateParentHeight(item);
        }
    }

    /**
     * Close a submenu and its children
     */
    function closeSubmenu(item) {
        const link = item.querySelector(":scope > .nav-link");
        const submenu = item.querySelector(":scope > .nav-submenu");

        if (!submenu) return;

        item.classList.remove("open");
        if (link) {
            link.setAttribute("aria-expanded", "false");
        }
        submenu.style.maxHeight = null;

        // Also close any nested open submenus
        const nestedOpen = item.querySelectorAll(".has-submenu.open");
        nestedOpen.forEach(function (nested) {
            nested.classList.remove("open");
            const nestedLink = nested.querySelector(":scope > .nav-link");
            const nestedSubmenu = nested.querySelector(":scope > .nav-submenu");
            if (nestedLink) {
                nestedLink.setAttribute("aria-expanded", "false");
            }
            if (nestedSubmenu) {
                nestedSubmenu.style.maxHeight = null;
            }
        });
    }

    /**
     * Update parent submenu heights when nested submenu opens
     */
    function updateParentHeight(item) {
        let parent = item.parentElement.closest(".has-submenu.open");
        while (parent) {
            const parentSubmenu = parent.querySelector(":scope > .nav-submenu");
            if (parentSubmenu) {
                // Calculate total height including all nested open submenus
                let totalHeight = 0;
                const children = parentSubmenu.children;
                for (let i = 0; i < children.length; i++) {
                    totalHeight += children[i].offsetHeight;
                }
                parentSubmenu.style.maxHeight = totalHeight + "px";
            }
            parent = parent.parentElement.closest(".has-submenu.open");
        }
    }

    /**
     * Search Bar Toggle (Mobile)
     */
    function initSearch() {
        const searchToggle = document.querySelector(".search-toggle");
        const mobileSearch = document.querySelector(".mobile-search");
        const mobileMenuToggle = document.querySelector(".mobile-menu-toggle");
        const mobileHeaderMenu = document.querySelector(".mobile-header-menu");
        const searchInput = mobileSearch
            ? mobileSearch.querySelector("input")
            : null;

        // Search toggle
        if (searchToggle && mobileSearch) {
            searchToggle.addEventListener("click", function (e) {
                e.preventDefault();

                // Close mobile menu if open
                if (
                    mobileHeaderMenu &&
                    mobileHeaderMenu.classList.contains("active")
                ) {
                    mobileHeaderMenu.classList.remove("active");
                }

                mobileSearch.classList.toggle("active");
                if (mobileSearch.classList.contains("active") && searchInput) {
                    searchInput.focus();
                }
            });
        }

        // Mobile menu toggle (three dots)
        if (mobileMenuToggle && mobileHeaderMenu) {
            mobileMenuToggle.addEventListener("click", function (e) {
                e.preventDefault();

                // Close search if open
                if (mobileSearch && mobileSearch.classList.contains("active")) {
                    mobileSearch.classList.remove("active");
                }

                mobileHeaderMenu.classList.toggle("active");
            });
        }

        // Close on click outside
        document.addEventListener("click", function (e) {
            // Close mobile search
            if (
                mobileSearch &&
                !mobileSearch.contains(e.target) &&
                !searchToggle.contains(e.target)
            ) {
                mobileSearch.classList.remove("active");
            }

            // Close mobile header menu
            if (
                mobileHeaderMenu &&
                mobileMenuToggle &&
                !mobileHeaderMenu.contains(e.target) &&
                !mobileMenuToggle.contains(e.target)
            ) {
                mobileHeaderMenu.classList.remove("active");
            }
        });

        // Close menus on window resize to desktop
        window.addEventListener("resize", function () {
            if (window.innerWidth >= 768) {
                if (mobileSearch) mobileSearch.classList.remove("active");
                if (mobileHeaderMenu)
                    mobileHeaderMenu.classList.remove("active");
            }
        });
    }

    /**
     * Back to Top Button
     */
    function initBackToTop() {
        const backToTop = document.querySelector(".back-to-top");

        if (backToTop) {
            // Show/hide based on scroll position
            window.addEventListener("scroll", function () {
                if (window.scrollY > 100) {
                    backToTop.classList.add("visible");
                } else {
                    backToTop.classList.remove("visible");
                }
            });

            // Scroll to top on click
            backToTop.addEventListener("click", function (e) {
                e.preventDefault();
                window.scrollTo({
                    top: 0,
                    behavior: "smooth",
                });
            });
        }
    }

    /**
     * Initialize Dropdowns (if not using Bootstrap JS)
     */
    function initDropdowns() {
        // Only initialize if Bootstrap's dropdown isn't available
        if (typeof bootstrap !== "undefined" && bootstrap.Dropdown) {
            return;
        }

        const dropdownToggles = document.querySelectorAll(
            '[data-bs-toggle="dropdown"]',
        );

        dropdownToggles.forEach(function (toggle) {
            toggle.addEventListener("click", function (e) {
                e.preventDefault();
                e.stopPropagation();

                const parent = this.parentElement;
                const menu = parent.querySelector(".dropdown-menu");

                // Close other dropdowns
                document
                    .querySelectorAll(".dropdown-menu.show")
                    .forEach(function (openMenu) {
                        if (openMenu !== menu) {
                            openMenu.classList.remove("show");
                        }
                    });

                // Toggle this dropdown
                menu.classList.toggle("show");
            });
        });

        // Close dropdowns on click outside
        document.addEventListener("click", function (e) {
            if (!e.target.closest(".dropdown")) {
                document
                    .querySelectorAll(".dropdown-menu.show")
                    .forEach(function (menu) {
                        menu.classList.remove("show");
                    });
            }
        });
    }

    /**
     * Initialize Tooltips
     */
    function initTooltips() {
        // Only initialize if Bootstrap's tooltip is available
        if (typeof bootstrap !== "undefined" && bootstrap.Tooltip) {
            const tooltipTriggerList = document.querySelectorAll(
                '[data-bs-toggle="tooltip"]',
            );
            tooltipTriggerList.forEach(function (tooltipTriggerEl) {
                new bootstrap.Tooltip(tooltipTriggerEl);
            });
        }
    }

    /**
     * Fullscreen Toggle
     */
    window.toggleFullscreen = function () {
        if (!document.fullscreenElement) {
            document.documentElement.requestFullscreen();
            document.body.classList.add("fullscreen-active");
        } else {
            if (document.exitFullscreen) {
                document.exitFullscreen();
                document.body.classList.remove("fullscreen-active");
            }
        }
    };

    // Listen for fullscreen change
    document.addEventListener("fullscreenchange", function () {
        if (!document.fullscreenElement) {
            document.body.classList.remove("fullscreen-active");
        }
    });
})();

/* ===========================
       CHART FACTORIES
    =========================== */
async function renderDonut(
    el,
    chartRef,
    labels,
    series,
    colors = [],
    title = "",
) {
    const options = {
        chart: {
            type: "donut",
            width: "100%",
            height: "100%",
        },
        title: {
            text: title,
            align: "center",
            margin: 10,
            style: {
                fontSize: "18px",
                fontWeight: "bold",
                color: "#263238",
            },
        },
        labels,
        series,
        colors,
        legend: { position: "bottom" },
        tooltip: { y: { formatter: (v) => v + " pengundi" } },
        responsive: [
            {
                breakpoint: 768,
                options: { chart: { width: "100%", height: 250 } },
            },
        ],
    };

    if (chartRef.chart) {
        chartRef.chart.updateOptions(options);
        return chartRef.chart.render();
    } else {
        chartRef.chart = new ApexCharts(el, options);
        await chartRef.chart.render();
    }
}

async function renderPie(el, chartRef, labels, series, title = "") {
    const options = {
        chart: {
            type: "pie",
            width: "100%",
            height: 350,
        },
        title: {
            text: title,
            align: "center",
            margin: 10,
            style: {
                fontSize: "18px",
                fontWeight: "bold",
                color: "#263238",
            },
        },
        labels,
        series,
        legend: {
            position: "bottom",
            horizontalAlign: "center",
            offsetY: 0,
        },
        tooltip: {
            y: {
                formatter: (v) => v + " pengundi",
            },
        },
    };

    if (chartRef.chart) {
        chartRef.chart.updateOptions(options);
        return chartRef.chart.render();
    } else {
        chartRef.chart = new ApexCharts(el, options);
        await chartRef.chart.render();
    }
}

async function renderStackedBar(
    el,
    chartRef,
    categories,
    series,
    yTitle = "",
    xTitle = "",
    colors = [],
    title = "",
    isHorizontal = false,
    isAnimated = true,
    isPercent = false,
) {
    const options = {
        chart: {
            type: "bar",
            stacked: true,
            stackType: isPercent ? "100%" : "normal",

            height: isHorizontal ? 800 : 600,
            animations: {
                enabled: isAnimated,
            },
            events: {
                dataPointSelection: function (event, chartContext, config) {
                    if (window.innerWidth < 768) {
                        const index = config.dataPointIndex;
                        const w = config.w;

                        if (index < 0) return;

                        const category =
                            w.globals.categories?.[index] ??
                            w.globals.labels?.[index] ??
                            `Data Point ${index + 1}`;

                        const seriesData = w.config.series || [];

                        const items = seriesData
                            .map((s, i) => ({
                                name: s.name,
                                value: s.data?.[index] ?? null,
                                color: w.globals.colors[i],
                            }))
                            .filter(
                                (item) =>
                                    item.value !== null &&
                                    item.value !== undefined,
                            )
                            .sort((a, b) => b.value - a.value)
                            .slice(0, 4);

                        const html = items
                            .map(
                                (i) => `
                <div class="tooltip-row">
                    <span class="tooltip-color" style="background:${i.color}"></span>
                    <span>${i.name}</span>
                    <strong class="ms-auto">${i.value}</strong>
                </div>
            `,
                            )
                            .join("");

                        document.getElementById("tooltipModalLabel").innerText =
                            category;
                        document.getElementById("tooltipModalBody").innerHTML =
                            html;

                        const tooltipModal = new bootstrap.Modal(
                            document.getElementById("tooltipModal"),
                        );

                        tooltipModal.show();
                    }
                },
            },
        },

        plotOptions: {
            bar: {
                horizontal: isHorizontal,
            },
        },

        tooltip: {
            shared: true,
            intersect: false,
            fixed: {
                enabled: true,
                position: "topRight", // or topLeft
                offsetX: 0,
                offsetY: 0,
            },
        },

        series,
        colors,
        dataLabels: {
            enabled: true,
            style: {
                colors: ["#fff"], // default text color
            },
            dropShadow: {
                enabled: true,
                top: 0, // move shadow slightly down
                left: 0, // move shadow slightly right
                blur: 1, // soften edges
                color: "#000",
            },
        },
        xaxis: {
            categories,
            title: {
                text: isHorizontal ? yTitle : xTitle,
            },
        },
        yaxis: {
            title: {
                text: isHorizontal ? xTitle : yTitle,
            },
        },

        legend: {
            show: true,
            position: "bottom",
            horizontalAlign: "center",
            width: "100%", // make legend span full chart width
            offsetX: 0, // optional, adjust horizontal offset
            offsetY: 0,
        },

        title: {
            text: title,
            align: "center",
            margin: 10,
            style: { fontSize: "18px", fontWeight: "bold", color: "#263238" },
        },

        responsive: [
            {
                breakpoint: 768,

                options: {
                    xaxis: {
                        title: { align: "left" },
                        labels: { align: "left" },
                    },
                    yaxis: {
                        title: { align: "left" },
                    },
                    plotOptions: {
                        bar: { horizontal: true },
                    },
                    chart: {
                        height: isHorizontal ? 800 : 500,
                        width: isHorizontal ? 800 : 500,
                    },

                    legend: { position: "bottom" },
                },
            },
        ],
    };

    try {
        // console.log('Chart element:', el);
        console.log("Chart options:", options);
        console.log("Existing chart instance:", chartRef.chart);

        if (chartRef.chart) {
            // console.log('Updating existing chart...');
            chartRef.chart.updateOptions(options);
            console.log("Options updated, rendering chart...");
            chartRef.chart.render();
            // console.log('Chart render completed.');
        } else {
            // console.log('Creating new chart...');
            chartRef.chart = new ApexCharts(el, options);
            await chartRef.chart.render();
            console.log("New chart rendered.");
        }
    } catch (error) {
        console.error("Error in chart rendering:", error);
    }
}

async function renderTreemap(el, chartRef, series) {
    const colors = [
        "#008FFB",
        "#00E396",
        "#FEB019",
        "#FF4560",
        "#775DD0",
        "#546E7A",
        "#26a69a",
        "#ff7043",
    ];
    const options = {
        chart: { type: "treemap", height: 450, toolbar: { show: true } },
        series,
        legend: { show: false },
        dataLabels: {
            enabled: true,
            style: { fontSize: "12px", colors: ["#fff"] },
            offsetY: -4,
        },
        plotOptions: {
            treemap: {
                distributed: true,
                enableShades: true,
                shadeIntensity: 0.5,
                reverseNegativeShade: true,
            },
        },
        tooltip: {
            y: { formatter: (val) => val + " pengundi" },
            x: { formatter: (val) => val },
        },
        colors,
    };

    if (chartRef.chart) {
        chartRef.chart.updateOptions(options);
        return chartRef.chart.render();
    } else {
        chartRef.chart = new ApexCharts(el, options);
        await chartRef.chart.render();
    }
}

/* ===========================
       PAYLOAD BUILDER
    =========================== */

function buildPayload() {
    const mode = modeSelect.value;
    const payload = { mode };

    year2Select.classList.toggle("d-none", mode !== "compare");

    if (mode === "compare") {
        payload.year1 = year1Select.value;
        payload.year2 = year2Select.value;
    } else {
        payload.year = year1Select.value;
    }

    return payload;
}

const DUN_COLORS = {
    TENDONG: "#1E88E5",
    PENGKALAN_CHEPA: "#43A047",
};

function shadeColor(color, percent) {
    if (!color || typeof color !== "string" || !color.startsWith("#")) {
        color = "#757575"; // fallback gray
    }

    let f = parseInt(color.slice(1), 16),
        t = percent < 0 ? 0 : 255,
        p = Math.abs(percent) / 100,
        R = f >> 16,
        G = (f >> 8) & 0x00ff,
        B = f & 0x0000ff;

    return (
        "#" +
        (
            0x1000000 +
            (Math.round((t - R) * p) + R) * 0x10000 +
            (Math.round((t - G) * p) + G) * 0x100 +
            (Math.round((t - B) * p) + B)
        )
            .toString(16)
            .slice(1)
    );
}
