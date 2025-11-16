$(document).ready(function () {
    // Fetch Dashboard Data
    $.get('dashboard_data.php', function (data) {
        // 1. Update Statistics Cards with animation
        animateValue("totalStudents", 0, data.total_students, 1000);
        animateValue("totalInstructors", 0, data.total_instructors, 1000);
        animateValue("totalCourses", 0, data.total_courses, 1000);
        animateValue("totalSections", 0, data.total_sections, 1000);

        // 2. Render Program Distribution Chart (Bar Chart)
        renderProgramChart(data.program_distribution);

        // 3. Render Year Level Chart (Doughnut Chart)
        renderYearLevelChart(data.student_year_level);
    });

    // Function to animate numbers
    function animateValue(id, start, end, duration) {
        if (start === end) return;
        var range = end - start;
        var current = start;
        var increment = end > start ? 1 : -1;
        var stepTime = Math.abs(Math.floor(duration / range));
        var obj = document.getElementById(id);
        var timer = setInterval(function () {
            current += increment;
            obj.innerHTML = current;
            if (current == end) {
                clearInterval(timer);
            }
        }, stepTime);
    }

    // Function to render Bar Chart
    function renderProgramChart(data) {
        const ctx = document.getElementById('programChart').getContext('2d');
        const labels = data.map(item => item.label);
        const counts = data.map(item => item.count);

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Number of Students',
                    data: counts,
                    backgroundColor: '#2F4156', // Navy
                    borderRadius: 5,
                    barThickness: 30
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { borderDash: [2, 2] }
                    },
                    x: {
                        grid: { display: false }
                    }
                }
            }
        });
    }

    // Function to render Doughnut Chart
    function renderYearLevelChart(data) {
        const ctx = document.getElementById('yearLevelChart').getContext('2d');
        const labels = data.map(item => item.label);
        const counts = data.map(item => item.count);

        // Colors matching your palette
        const bgColors = [
            '#2F4156', // Navy
            '#567C8D', // Teal
            '#C8D9E6', // Sky Blue
            '#F5EFEB', // Beige
            '#D3D3D3'
        ];

        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: counts,
                    backgroundColor: bgColors,
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { usePointStyle: true, padding: 20 }
                    }
                },
                cutout: '70%' // Makes it look like a ring
            }
        });
    }
});