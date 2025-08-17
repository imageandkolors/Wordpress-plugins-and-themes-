document.addEventListener('DOMContentLoaded', function() {
    'use strict';

    const examContainers = document.querySelectorAll('.se-exam-container');

    examContainers.forEach(container => {
        const widgetId = container.id.replace('se-exam-container-', '');
        const examData = window['se_exam_data_' + widgetId];

        if (examData) {
            new Exam(container, examData);
        }
    });

    class Exam {
        constructor(container, data) {
            this.container = container;
            this.data = data;
            this.currentQuestionIndex = 0;
            this.answers = {};
            this.timeSpent = {};
            this.questionStartTime = null;
            this.globalTimerInterval = null;
            this.questionTimerInterval = null;
            this.isAnimating = false;

            this.init();
        }

        init() {
            const startBtn = this.container.querySelector('.se-start-exam-btn');
            if (startBtn) {
                startBtn.addEventListener('click', () => this.startExam());
            }
        }

        startExam() {
            this.renderQuestionView();
            this.startGlobalTimer();
        }

        renderQuestionView() {
            this.container.innerHTML = `
                <div class="se-exam-header">
                    <div class="se-exam-title">${this.data.title}</div>
                    <div class="se-global-timer">Time Left: <span></span></div>
                </div>
                <div class="se-progress-bar"><div class="se-progress-bar-inner"></div></div>
                <div class="se-question-container"></div>
                <div class="se-exam-footer">
                    <button class="se-prev-question-btn">Previous</button>
                    <button class="se-next-question-btn">Next</button>
                    <button class="se-submit-exam-btn">Submit</button>
                </div>
                <div class="se-toast-notification"></div>
            `;

            this.questionContainer = this.container.querySelector('.se-question-container');
            this.globalTimerDisplay = this.container.querySelector('.se-global-timer span');

            this.container.querySelector('.se-prev-question-btn').addEventListener('click', () => this.prevQuestion());
            this.container.querySelector('.se-next-question-btn').addEventListener('click', () => this.nextQuestion());
            this.container.querySelector('.se-submit-exam-btn').addEventListener('click', () => this.submitExam());

            this.showQuestion(this.currentQuestionIndex, 'next', true);
        }

        showQuestion(index, direction = 'next', instant = false) {
            if (this.isAnimating || index < 0 || index >= this.data.questions.length) {
                return;
            }
            this.isAnimating = true;

            // Save time for the previous question before showing the new one
            if (this.questionStartTime) {
                const questionId = this.data.questions[this.currentQuestionIndex].id;
                const endTime = new Date();
                this.timeSpent[questionId] = (this.timeSpent[questionId] || 0) + (endTime - this.questionStartTime);
            }

            const outClass = direction === 'next' ? 'slide-out-left' : 'slide-out-right';

            const animateOut = () => {
                if (!instant) {
                    this.questionContainer.classList.add(outClass);
                    setTimeout(updateContent, 500);
                } else {
                    updateContent();
                }
            };

            const updateContent = () => {
                this.currentQuestionIndex = index;
                const question = this.data.questions[index];

                let optionsHtml = '';
                if (question.type === 'theory') {
                    const answer = this.answers[question.id] || '';
                    optionsHtml = `<textarea class="se-theory-answer" data-question-id="${question.id}" rows="8">${answer}</textarea>`;
                } else if (question.options) {
                    optionsHtml = '<ul class="se-options">';
                    question.options.forEach((option, i) => {
                        const isChecked = this.answers[question.id] == i;
                        optionsHtml += `
                            <li>
                                <label>
                                    <input type="radio" name="question_${question.id}" value="${i}" ${isChecked ? 'checked' : ''}>
                                    ${option.text}
                                </label>
                            </li>
                        `;
                    });
                    optionsHtml += '</ul>';
                }

                this.questionContainer.innerHTML = `
                    <div class="se-question-header">
                        <h3>${question.title}</h3>
                        <div class="se-question-timer">Time Left: <span></span></div>
                    </div>
                    <div class="se-question-content">${question.content}</div>
                    ${optionsHtml}
                `;

                this.questionStartTime = new Date();
                this.startQuestionTimer(question.timer);
                this.updateNavButtons();
                this.updateProgressBar();

                if (!instant) {
                    const inClass = direction === 'next' ? 'slide-in-right' : 'slide-in-left';
                    this.questionContainer.classList.remove(outClass);
                    this.questionContainer.classList.add(inClass);
                    setTimeout(() => {
                        this.questionContainer.classList.remove(inClass);
                        this.isAnimating = false;
                    }, 500);
                } else {
                    this.isAnimating = false;
                }
            };

            animateOut();
        }

        updateProgressBar() {
            const progress = ((this.currentQuestionIndex + 1) / this.data.questions.length) * 100;
            const progressBarInner = this.container.querySelector('.se-progress-bar-inner');
            if (progressBarInner) {
                progressBarInner.style.width = `${progress}%`;
            }
        }

        startGlobalTimer() {
            let duration = this.data.duration * 60;
            this.globalTimerInterval = setInterval(() => {
                duration--;
                const minutes = Math.floor(duration / 60);
                const seconds = duration % 60;
                this.globalTimerDisplay.textContent = `${minutes}:${seconds < 10 ? '0' : ''}${seconds}`;

                if (duration <= 0) {
                    clearInterval(this.globalTimerInterval);
                    this.submitExam();
                }
            }, 1000);
        }

        startQuestionTimer(duration) {
            clearInterval(this.questionTimerInterval);
            const timerContainer = this.questionContainer.querySelector('.se-question-timer');
            if (!duration || !timerContainer) {
                if(timerContainer) timerContainer.style.display = 'none';
                return;
            }
            timerContainer.style.display = 'block';

            const timerDisplay = timerContainer.querySelector('span');
            this.questionTimerInterval = setInterval(() => {
                duration--;
                timerDisplay.textContent = `${duration}s`;

                if (duration < 15) {
                    timerContainer.classList.add('warning');
                }

                if (duration <= 0) {
                    this.showToast("Time's up! Moving to the next question.");
                    this.nextQuestion();
                }
            }, 1000);
        }

        showToast(message) {
            const toast = this.container.querySelector('.se-toast-notification');
            if (!toast) return;

            toast.textContent = message;
            toast.classList.add('show');

            setTimeout(() => {
                toast.classList.remove('show');
            }, 3000);
        }

        prevQuestion() {
            this.saveAnswer();
            if (this.currentQuestionIndex > 0) {
                this.showQuestion(this.currentQuestionIndex - 1, 'prev');
            }
        }

        nextQuestion() {
            this.saveAnswer();
            if (this.currentQuestionIndex < this.data.questions.length - 1) {
                this.showQuestion(this.currentQuestionIndex + 1, 'next');
            } else {
                this.submitExam();
            }
        }

        updateNavButtons() {
            const prevBtn = this.container.querySelector('.se-prev-question-btn');
            const nextBtn = this.container.querySelector('.se-next-question-btn');

            prevBtn.disabled = this.currentQuestionIndex === 0;
            if (this.currentQuestionIndex === this.data.questions.length - 1) {
                nextBtn.style.display = 'none';
            } else {
                nextBtn.style.display = 'inline-block';
            }
        }

        saveAnswer() {
            const question = this.data.questions[this.currentQuestionIndex];
            if (question.type === 'theory') {
                const textarea = this.questionContainer.querySelector(`.se-theory-answer[data-question-id="${question.id}"]`);
                if (textarea) {
                    this.answers[question.id] = textarea.value;
                }
            } else {
                const selectedOption = this.questionContainer.querySelector(`input[name="question_${question.id}"]:checked`);
                if (selectedOption) {
                    this.answers[question.id] = selectedOption.value;
                }
            }
        }

        submitExam() {
            this.saveAnswer();
            clearInterval(this.globalTimerInterval);
            clearInterval(this.questionTimerInterval);

            // Save time for the last question
            if (this.questionStartTime) {
                const questionId = this.data.questions[this.currentQuestionIndex].id;
                const endTime = new Date();
                this.timeSpent[questionId] = (this.timeSpent[questionId] || 0) + (endTime - this.questionStartTime);
            }

            this.container.innerHTML = '<h2>Submitting...</h2>';

            wp.apiFetch({
                path: `/se/v1/exams/${this.data.id}/submit`,
                method: 'POST',
                data: {
                    answers: this.answers,
                    time_spent: this.timeSpent
                },
            }).then(response => {
                this.renderResults(response);
            }).catch(error => {
                this.container.innerHTML = `<h2>Error</h2><p>${error.message}</p>`;
            });
        }

        renderResults(results) {
            this.container.innerHTML = `
                <h2>Exam Results</h2>
                <p><strong>Status:</strong> ${results.status}</p>
                <p><strong>Score:</strong> ${results.score} / ${results.max_score}</p>
                <p><strong>Percentage:</strong> ${results.percentage}%</p>
            `;
        }
    }
});
