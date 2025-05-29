let selectedCandidates = {};
const totalPositions = document.querySelectorAll('.card').length;
const submitButton = document.querySelector('.submit-vote-btn');
const votingForm = document.getElementById('votingForm');

// Add form submit event listener
votingForm.addEventListener('submit', function(e) {
    e.preventDefault();
    if (confirmVote()) {
        this.submit();
    }
});

function updateVoteStatus(positionId, status) {
    const statusElement = document.getElementById(`status-${positionId}`);
    if (statusElement) {
        statusElement.textContent = status ? 'Vote For' : 'Not Voted';
        statusElement.classList.toggle('completed', status);
    }
}

function updateSubmitButton() {
    const hasAllVotes = Object.keys(selectedCandidates).length === totalPositions;
    submitButton.disabled = !hasAllVotes;
    
    if (!hasAllVotes) {
        const remaining = totalPositions - Object.keys(selectedCandidates).length;
        submitButton.setAttribute('data-tooltip', 
            `Please select ${remaining} more candidate${remaining > 1 ? 's' : ''}`);
    } else {
        submitButton.setAttribute('data-tooltip', 'Click to submit your vote');
    }
}

function selectCandidate(card, positionId, candidateId) {
    const cards = card.closest('.row').querySelectorAll('.candidate-card');
    cards.forEach(c => c.classList.remove('selected'));
    card.classList.add('selected');
    card.querySelector('input[type="radio"]').checked = true;
    selectedCandidates[positionId] = candidateId;
    
    updateVoteStatus(positionId, true);
    updateSubmitButton();
}

function resetVotes() {
    if (confirm('Are you sure you want to reset all your selections?')) {
        // Clear all selections
        document.querySelectorAll('.candidate-card').forEach(card => {
            card.classList.remove('selected');
            card.querySelector('input[type="radio"]').checked = false;
        });
        
        // Reset status indicators
        Object.keys(selectedCandidates).forEach(positionId => {
            updateVoteStatus(positionId, false);
        });
        
        // Clear selected candidates
        selectedCandidates = {};
        updateSubmitButton();
    }
}

function confirmVote() {
    if (Object.keys(selectedCandidates).length !== totalPositions) {
        alert('Please select a candidate for each position before submitting.');
        return false;
    }

    return confirm('Are you sure you want to submit your vote? This action cannot be undone.');
}

function toggleTheme() {
    const currentTheme = document.body.getAttribute('data-theme');
    const newTheme = currentTheme === 'light' ? 'dark' : 'light';
    document.body.setAttribute('data-theme', newTheme);
    document.cookie = `theme=${newTheme}; path=/; max-age=${60*60*24*30}`;
    const icon = document.querySelector('.theme-toggle i');
    icon.classList.toggle('fa-moon');
    icon.classList.toggle('fa-sun');
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    // Set theme from localStorage
    const currentTheme = document.body.getAttribute('data-theme');
    const icon = document.querySelector('.theme-toggle i');
    if (currentTheme === 'dark') {
        icon.classList.remove('fa-moon');
        icon.classList.add('fa-sun');
    }
    
    // Initialize submit button state
    updateSubmitButton();
});