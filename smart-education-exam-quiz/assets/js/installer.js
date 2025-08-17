(function(wp) {
    'use strict';

    if (!wp || !wp.element) {
        return;
    }

    const { createElement, render, useState, useEffect } = wp.element;

    const App = () => {
        const [step, setStep] = useState(1);
        const [mode, setMode] = useState(null);
        const [template, setTemplate] = useState(null);
        const [jobId, setJobId] = useState(null);

        // ... (All the component definitions remain the same)
        const Welcome = () => (
            createElement('div', { className: 'se-installer-step' },
                createElement('h1', null, 'Welcome to Smart Education'),
                createElement('p', null, 'The modern exam and quiz plugin for WordPress.'),
                createElement('button', { onClick: () => setStep(2) }, 'Start Setup')
            )
        );
        const ModeSelection = () => (
            createElement('div', { className: 'se-installer-step' },
                createElement('h2', null, 'Choose Your Setup Mode'),
                createElement('div', { className: 'se-mode-selection' },
                    createElement('div', { className: 'se-mode-card', onClick: () => { setMode('scratch'); setStep(4); } },
                        createElement('h3', null, 'Build from Scratch'),
                        createElement('p', null, 'Create your own exams and quizzes from the ground up.')
                    ),
                    createElement('div', { className: 'se-mode-card', onClick: () => { setMode('template'); setStep(3); } },
                        createElement('h3', null, 'Use a Premade Template'),
                        createElement('p', null, 'Get started quickly with one of our professionally designed templates.')
                    )
                )
            )
        );
        const TemplatePicker = () => {
            const [templates, setTemplates] = useState([]);
            const [showPreview, setShowPreview] = useState(false);
            const [previewTemplate, setPreviewTemplate] = useState(null);

            useEffect(() => {
                wp.apiFetch({ path: '/se/v1/installer/templates' })
                    .then(setTemplates)
                    .catch(error => console.error(error));
            }, []);

            const handlePreview = (template) => {
                setPreviewTemplate(template);
                setShowPreview(true);
            };

            const handleImport = (templateId) => {
                setTemplate(templateId);
                setStep(4);
            };

            const Modal = ({ template, onClose }) => (
                createElement('div', { className: 'se-modal-overlay' },
                    createElement('div', { className: 'se-modal' },
                        createElement('h3', null, `${template.name} Preview`),
                        createElement('p', null, template.features),
                        createElement('p', null, 'Included pages: Home, Dashboard, Exam, Results, Certificate, Login.'),
                        createElement('button', { onClick: onClose }, 'Close')
                    )
                )
            );

            return createElement('div', { className: 'se-installer-step' },
                createElement('h2', null, 'Select a Template'),
                createElement('div', { className: 'se-template-picker' },
                    templates.map(t => (
                        createElement('div', { className: 'se-template-card', key: t.id },
                            createElement('h3', null, t.name),
                            createElement('p', null, t.features),
                            createElement('div', { className: 'se-template-card-actions' },
                                createElement('button', { onClick: () => handlePreview(t) }, 'Preview'),
                                createElement('button', { className: 'primary', onClick: () => handleImport(t.id) }, 'Import')
                            )
                        )
                    ))
                ),
                showPreview && createElement(Modal, { template: previewTemplate, onClose: () => setShowPreview(false) })
            );
        };
        const Settings = () => {
            const [settings, setSettings] = useState({
                institutionType: 'college',
                importDemo: true,
            });

            const handleInputChange = (e) => {
                const { name, value, type, checked } = e.target;
                setSettings(prev => ({ ...prev, [name]: type === 'checkbox' ? checked : value }));
            };

            const handleStartImport = () => {
                wp.apiFetch({
                    path: '/se/v1/installer/start',
                    method: 'POST',
                    data: { mode, template, settings },
                }).then(response => {
                    setJobId(response.job_id);
                    setStep(5);
                }).catch(error => alert(error.message));
            };

            return createElement('div', { className: 'se-installer-step' },
                createElement('h2', null, 'Configure Your Settings'),
                createElement('div', { className: 'se-settings-form' },
                     createElement('label', null, 'Import Demo Data?'),
                     createElement('input', { type: 'checkbox', name: 'importDemo', checked: settings.importDemo, onChange: handleInputChange }),
                     createElement('button', { onClick: handleStartImport }, 'Start Import')
                )
            );
        };
        const Progress = () => {
            const [progress, setProgress] = useState(0);
            const [log, setLog] = useState('Starting import...');

            useEffect(() => {
                const interval = setInterval(() => {
                    wp.apiFetch({ path: `/se/v1/installer/status?job_id=${jobId}` })
                        .then(response => {
                            setProgress(response.percent);
                            setLog(response.step);
                            if (response.status === 'completed' || response.status === 'failed') {
                                clearInterval(interval);
                                setStep(6);
                            }
                        });
                }, 2000);
                return () => clearInterval(interval);
            }, [jobId]);

            return createElement('div', { className: 'se-installer-step' },
                createElement('h2', null, 'Importing...'),
                createElement('div', { className: 'se-progress-bar' },
                    createElement('div', { className: 'se-progress-bar-inner', style: { width: `${progress}%` } })
                ),
                createElement('pre', { className: 'se-log-output' }, log)
            );
        };
        const Finish = () => (
            createElement('div', { className: 'se-installer-step' },
                createElement('h2', null, 'Setup Complete!'),
                createElement('p', null, 'Your site is ready. You can now start creating exams.'),
                createElement('a', { href: '/wp-admin/edit.php?post_type=se_exam', className: 'button' }, 'Go to Dashboard')
            )
        );


        switch (step) {
            case 1: return createElement(Welcome);
            case 2: return createElement(ModeSelection);
            case 3: return createElement(TemplatePicker);
            case 4: return createElement(Settings);
            case 5: return createElement(Progress);
            case 6: return createElement(Finish);
            default: return createElement(Welcome);
        }
    };

    window.seInstaller = {
        init: function() {
            const target = document.getElementById('se-installer-react-app');
            if (target) {
                render(createElement(App), target);
            }
        }
    };

})(window.wp);
