async function loadRoomTypes() {
    try {
        const response = await fetch('api/room-types.php');
        const data = await response.json();

        if (data.error) {
            throw new Error(data.error);
        }

        roomTypes = data; // Gán dữ liệu từ database
        renderRooms();
    } catch (error) {
        console.error('Error loading room types:', error);
        document.getElementById('carouselWrapper').innerHTML =
            '<div style="text-align: center; padding: 40px; color: #666;">Không thể tải dữ liệu phòng</div>';
    }
}
// Khởi tạo biến toàn cục
let roomTypes = [];
let currentIndex = 0;

const cardWidth = 296; // 280px + 16px gap

function createRoomCard(room) {
    const imageElement = room.image
        ? `<img src="${room.image}" alt="${room.typename}" class="room-image">`
        : `<div class="room-image no-image"></div>`;

    return `
                <div class="room-card" onclick="goToRoomDetail(${room.id})">
                    <div style="position: relative;">
                        ${imageElement}
                        <div class="rating-badge">
                            ${room.rating || 'N/A'}/10 <span class="review-count">${room.reviewCount || 0} đánh giá</span>
                        </div>
                    </div>
                    <div class="room-content">
                        <div>
                            <h3 class="room-title">${room.typename}</h3>
                            <div class="room-stars">${generateStars(room.stars || 0)}</div>
                            <div class="room-location">${room.description || 'Không có mô tả'}</div>
                        </div>
                        <div class="room-price">
                            <span class="price-label">Từ</span>
                            <span class="price-amount">${formatPrice(room.price_per_day || 0)}₫</span>
                        </div>
                    </div>
                </div>
            `;
}

function generateStars(starCount) {
    return '⭐'.repeat(Math.max(0, Math.min(5, starCount)));
}

function formatPrice(price) {
    return new Intl.NumberFormat('vi-VN').format(price);
}

function renderRooms() {
    const wrapper = document.getElementById('carouselWrapper');
    if (roomTypes.length === 0) {
        wrapper.innerHTML = '<div style="text-align: center; padding: 40px; color: #666;">Đang tải dữ liệu...</div>';
        return;
    }
    wrapper.innerHTML = roomTypes.map(createRoomCard).join('');
    updateControls();
}

function updateControls() {
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    const wrapper = document.getElementById('carouselWrapper');
    const maxIndex = Math.max(0, roomTypes.length - Math.floor(wrapper.parentElement.offsetWidth / cardWidth));

    prevBtn.disabled = currentIndex === 0;
    nextBtn.disabled = currentIndex >= maxIndex;
}

function goToRoomDetail(roomId) {
    // Simulate navigation to room detail page
    console.log(`Navigating to room detail: ${roomId}`);
    // window.location.href = `room-detail.php?id=${roomId}`;
    alert(`Chuyển đến trang chi tiết phòng ID: ${roomId}`);
}

// Handle window resize
window.addEventListener('resize', () => {
    currentIndex = 0;
    document.getElementById('carouselWrapper').style.transform = 'translateX(0)';
    updateControls();
});

// Touch/mouse drag for mobile
let isDragging = false;
let startPos = 0;
let currentTranslate = 0;
let prevTranslate = 0;

const wrapper = document.getElementById('carouselWrapper');

wrapper.addEventListener('mousedown', dragStart);
wrapper.addEventListener('touchstart', dragStart);
wrapper.addEventListener('mouseup', dragEnd);
wrapper.addEventListener('touchend', dragEnd);
wrapper.addEventListener('mousemove', drag);
wrapper.addEventListener('touchmove', drag);
wrapper.addEventListener('mouseleave', dragEnd);

function dragStart(e) {
    isDragging = true;
    startPos = e.type.includes('mouse') ? e.clientX : e.touches[0].clientX;
    wrapper.style.transition = 'none';
}

function drag(e) {
    if (!isDragging) return;

    const currentPosition = e.type.includes('mouse') ? e.clientX : e.touches[0].clientX;
    const diff = currentPosition - startPos;
    currentTranslate = prevTranslate + diff;

    wrapper.style.transform = `translateX(${currentTranslate}px)`;
}

function dragEnd() {
    if (!isDragging) return;

    isDragging = false;
    wrapper.style.transition = 'transform 0.3s ease';

    const movedBy = currentTranslate - prevTranslate;

    if (Math.abs(movedBy) > 50) {
        if (movedBy > 0 && currentIndex > 0) {
            scrollCarousel(-1);
        } else if (movedBy < 0) {
            scrollCarousel(1);
        }
    } else {
        wrapper.style.transform = `translateX(-${currentIndex * cardWidth}px)`;
    }

    prevTranslate = -currentIndex * cardWidth;
    currentTranslate = prevTranslate;
}

function scrollCarousel(direction) {
    const wrapper = document.getElementById('carouselWrapper');
    const maxIndex = Math.max(0, roomTypes.length - Math.floor(wrapper.parentElement.offsetWidth / cardWidth));

    currentIndex = Math.max(0, Math.min(maxIndex, currentIndex + direction));

    wrapper.style.transform = `translateX(-${currentIndex * cardWidth}px)`;
    updateControls();
}
// Initialize
document.addEventListener('DOMContentLoaded', () => {
    loadRoomTypes(); 
    prevTranslate = 0;
    currentTranslate = 0;
});