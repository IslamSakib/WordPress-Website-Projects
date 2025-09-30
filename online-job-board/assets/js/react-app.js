const { render, useState, useEffect, createElement: h } = wp.element;
const { apiFetch } = wp;

// Search Bar Component
const SearchBar = ({ onSearch }) => {
	const [query, setQuery] = useState('');

	const handleSubmit = (e) => {
		e.preventDefault();
		onSearch(query);
	};

	const handleClear = () => {
		setQuery('');
		onSearch('');
	};

	return h('div', { className: 'ojb-search-wrapper' },
		h('form', { onSubmit: handleSubmit, className: 'ojb-search-form' },
			h('input', {
				type: 'text',
				className: 'ojb-search-input',
				placeholder: 'Search jobs by title, company...',
				value: query,
				onChange: (e) => setQuery(e.target.value)
			}),
			h('button', { type: 'submit', className: 'ojb-search-btn' }, 'Search'),
			query && h('button', {
				type: 'button',
				className: 'ojb-clear-btn',
				onClick: handleClear
			}, 'Clear')
		)
	);
};

// Job Card Component
const JobCard = ({ job, onViewDetails, onApply }) => {
	const meta = job.meta || {};
	const companyName = meta._job_company_name || '';
	const companyAddr = meta._job_company_address || '';
	const deadline = meta._job_deadline || '';
	const closingDate = meta._job_closing_date || '';

	const isExpired = deadline && new Date(deadline) < new Date();
	const isClosed = closingDate && new Date(closingDate) < new Date();
	const isInactive = isExpired || isClosed;

	const excerpt = job.excerpt?.rendered || '';
	const strippedExcerpt = excerpt.replace(/<[^>]*>/g, '').substring(0, 100) + '...';

	const formatDate = (dateString) => {
		if (!dateString) return '';
		const date = new Date(dateString);
		return date.toLocaleDateString('en-US', {
			year: 'numeric',
			month: 'long',
			day: 'numeric',
		});
	};

	return h('div', { className: `ojb-job-card ${isInactive ? 'ojb-expired' : ''}` },
		h('h3', { className: 'ojb-job-title' },
			job.title.rendered,
			isClosed && h('span', { className: 'ojb-badge-expired' }, 'Closed'),
			!isClosed && isExpired && h('span', { className: 'ojb-badge-expired' }, 'Expired')
		),
		h('div', { className: 'ojb-job-meta' },
			h('p', { className: 'ojb-company' },
				h('strong', null, 'Company:'),
				' ' + companyName
			),
			h('p', { className: 'ojb-location' },
				h('strong', null, 'Location:'),
				' ' + companyAddr
			),
			deadline && h('p', { className: `ojb-deadline ${isExpired ? 'expired' : ''}` },
				h('strong', null, 'Deadline:'),
				' ' + formatDate(deadline)
			),
			closingDate && h('p', { className: `ojb-closing-date ${isClosed ? 'expired' : ''}` },
				h('strong', null, 'Closing Date:'),
				' ' + formatDate(closingDate)
			)
		),
		h('div', { className: 'ojb-job-excerpt' },
			h('p', null, strippedExcerpt)
		),
		h('div', { className: 'ojb-card-actions' },
			h('button', { className: 'ojb-view-details-btn', onClick: onViewDetails }, 'View Details'),
			h('button', { className: 'ojb-apply-btn', onClick: onApply }, 'Apply Now')
		)
	);
};

// Job Grid Component
const JobGrid = ({ jobs, onViewDetails, onApply }) => {
	if (!jobs || jobs.length === 0) {
		return h('div', { className: 'ojb-no-jobs' },
			h('p', null, 'No job listings found.')
		);
	}

	return h('div', { className: 'ojb-jobs-grid' },
		jobs.map(job => h(JobCard, {
			key: job.id,
			job,
			onViewDetails: () => onViewDetails(job),
			onApply: () => onApply(job)
		}))
	);
};

// Pagination Component
const Pagination = ({ currentPage, totalPages, onPageChange }) => {
	const range = 2;
	const pages = [];

	for (let i = 1; i <= totalPages; i++) {
		if (
			i === 1 ||
			i === totalPages ||
			(i >= currentPage - range && i <= currentPage + range)
		) {
			pages.push(i);
		} else if (i === currentPage - range - 1 || i === currentPage + range + 1) {
			pages.push('...');
		}
	}

	return h('div', { className: 'ojb-pagination' },
		currentPage > 1 && h('button', {
			className: 'ojb-page-link ojb-prev',
			onClick: () => onPageChange(currentPage - 1)
		}, '‹ Prev'),
		pages.map((page, index) =>
			page === '...'
				? h('span', { key: `ellipsis-${index}`, className: 'ojb-page-ellipsis' }, '…')
				: h('button', {
					key: page,
					className: `ojb-page-link ojb-page-num ${page === currentPage ? 'ojb-active' : ''}`,
					onClick: () => onPageChange(page)
				}, page)
		),
		currentPage < totalPages && h('button', {
			className: 'ojb-page-link ojb-next',
			onClick: () => onPageChange(currentPage + 1)
		}, 'Next ›')
	);
};

// Job Details Modal Component
const JobDetailsModal = ({ job, onClose, onApply }) => {
	const meta = job.meta || {};
	const companyName = meta._job_company_name || '';
	const companyAddr = meta._job_company_address || '';
	const deadline = meta._job_deadline || '';
	const closingDate = meta._job_closing_date || '';

	const formatDate = (dateString) => {
		if (!dateString) return '';
		const date = new Date(dateString);
		return date.toLocaleDateString('en-US', {
			year: 'numeric',
			month: 'long',
			day: 'numeric',
		});
	};

	const handleBackdropClick = (e) => {
		if (e.target.classList.contains('ojb-modal')) {
			onClose();
		}
	};

	return h('div', { className: 'ojb-modal', onClick: handleBackdropClick },
		h('div', { className: 'ojb-modal-dialog ojb-modal-large' },
			h('div', { className: 'ojb-modal-header' },
				h('h3', null, 'Job Details'),
				h('button', { className: 'ojb-modal-close', onClick: onClose }, '×')
			),
			h('div', { className: 'ojb-modal-body' },
				h('div', { className: 'ojb-job-detail-content' },
					h('div', { className: 'ojb-job-info-box' },
						h('div', { className: 'ojb-info-item' },
							h('strong', null, 'Job Title:'),
							h('span', null, job.title.rendered)
						),
						h('div', { className: 'ojb-info-item' },
							h('strong', null, 'Company:'),
							h('span', null, companyName)
						),
						h('div', { className: 'ojb-info-item' },
							h('strong', null, 'Location:'),
							h('span', null, companyAddr)
						),
						deadline && h('div', { className: 'ojb-info-item' },
							h('strong', null, 'Deadline:'),
							h('span', null, formatDate(deadline))
						),
						closingDate && h('div', { className: 'ojb-info-item' },
							h('strong', null, 'Closing Date:'),
							h('span', null, formatDate(closingDate))
						)
					),
					h('div', { className: 'ojb-job-description' },
						h('h4', null, 'Job Description'),
						h('div', {
							id: 'ojb-detail-description',
							dangerouslySetInnerHTML: { __html: job.content.rendered }
						})
					),
					h('div', { className: 'ojb-detail-actions' },
						h('button', {
							className: 'ojb-apply-from-detail-btn',
							onClick: onApply
						}, 'Apply for this Position'),
						h('button', {
							className: 'ojb-close-detail-btn',
							onClick: onClose
						}, 'Close')
					)
				)
			)
		)
	);
};

// Application Modal Component
const ApplicationModal = ({ job, onClose }) => {
	const [formData, setFormData] = useState({
		fullName: '',
		email: '',
		phone: '',
		coverLetter: '',
		resume: null
	});
	const [message, setMessage] = useState('');
	const [messageType, setMessageType] = useState('');
	const [submitting, setSubmitting] = useState(false);

	const handleChange = (e) => {
		const { name, value, files } = e.target;
		if (name === 'resume') {
			setFormData({ ...formData, resume: files[0] });
		} else {
			setFormData({ ...formData, [name]: value });
		}
	};

	const handleSubmit = async (e) => {
		e.preventDefault();
		setMessage('');
		setSubmitting(true);

		const formDataObj = new FormData();
		formDataObj.append('action', 'ojb_submit_application');
		formDataObj.append('nonce', window.ojbData.nonce);
		formDataObj.append('job_id', job.id);
		formDataObj.append('full_name', formData.fullName);
		formDataObj.append('email', formData.email);
		formDataObj.append('phone', formData.phone);
		formDataObj.append('cover_letter', formData.coverLetter);
		if (formData.resume) {
			formDataObj.append('resume', formData.resume);
		}

		try {
			const response = await fetch(window.ojbData.ajaxUrl, {
				method: 'POST',
				body: formDataObj,
			});

			const result = await response.json();

			if (result.success) {
				setMessage(result.data.message);
				setMessageType('success');
				setFormData({
					fullName: '',
					email: '',
					phone: '',
					coverLetter: '',
					resume: null
				});
				setTimeout(() => onClose(), 3000);
			} else {
				setMessage(result.data.message || 'An error occurred');
				setMessageType('error');
			}
		} catch (error) {
			setMessage('Failed to submit application');
			setMessageType('error');
		}

		setSubmitting(false);
	};

	const handleBackdropClick = (e) => {
		if (e.target.classList.contains('ojb-modal')) {
			onClose();
		}
	};

	return h('div', { className: 'ojb-modal', onClick: handleBackdropClick },
		h('div', { className: 'ojb-modal-dialog' },
			h('div', { className: 'ojb-modal-header' },
				h('h3', null, 'Apply for Position'),
				h('button', { className: 'ojb-modal-close', onClick: onClose }, '×')
			),
			h('div', { className: 'ojb-modal-body' },
				h('form', { id: 'ojb-application-form', onSubmit: handleSubmit },
					message && h('div', { className: `ojb-message ${messageType}` }, message),
					h('div', { className: 'ojb-form-field' },
						h('label', null,
							'Full Name ',
							h('span', { className: 'ojb-required' }, '*')
						),
						h('input', {
							type: 'text',
							name: 'fullName',
							value: formData.fullName,
							onChange: handleChange,
							required: true
						})
					),
					h('div', { className: 'ojb-form-field' },
						h('label', null,
							'Email Address ',
							h('span', { className: 'ojb-required' }, '*')
						),
						h('input', {
							type: 'email',
							name: 'email',
							value: formData.email,
							onChange: handleChange,
							required: true
						})
					),
					h('div', { className: 'ojb-form-field' },
						h('label', null,
							'Phone Number ',
							h('span', { className: 'ojb-required' }, '*')
						),
						h('input', {
							type: 'tel',
							name: 'phone',
							value: formData.phone,
							onChange: handleChange,
							required: true
						})
					),
					h('div', { className: 'ojb-form-field' },
						h('label', null,
							'Cover Letter ',
							h('span', { className: 'ojb-required' }, '*')
						),
						h('textarea', {
							name: 'coverLetter',
							value: formData.coverLetter,
							onChange: handleChange,
							required: true,
							rows: 4
						})
					),
					h('div', { className: 'ojb-form-field' },
						h('label', null,
							'Resume (PDF) ',
							h('span', { className: 'ojb-required' }, '*')
						),
						h('input', {
							type: 'file',
							name: 'resume',
							accept: '.pdf',
							onChange: handleChange,
							required: true
						}),
						h('small', null, 'PDF only, max 5MB')
					),
					h('div', { className: 'ojb-form-actions' },
						h('button', {
							type: 'submit',
							className: 'ojb-submit-btn',
							disabled: submitting
						}, submitting ? 'Submitting...' : 'Submit Application'),
						h('button', {
							type: 'button',
							className: 'ojb-cancel-btn',
							onClick: onClose
						}, 'Close')
					)
				)
			)
		)
	);
};

// Main Job Board Component
const JobBoard = ({ perPage = 6 }) => {
	const [jobs, setJobs] = useState([]);
	const [loading, setLoading] = useState(true);
	const [currentPage, setCurrentPage] = useState(1);
	const [totalPages, setTotalPages] = useState(1);
	const [searchQuery, setSearchQuery] = useState('');
	const [selectedJob, setSelectedJob] = useState(null);
	const [showDetailsModal, setShowDetailsModal] = useState(false);
	const [showApplicationModal, setShowApplicationModal] = useState(false);

	useEffect(() => {
		fetchJobs();
	}, [currentPage, searchQuery]);

	const fetchJobs = async () => {
		setLoading(true);
		try {
			const url = `/wp-json/wp/v2/job_listing?per_page=${perPage}&page=${currentPage}&search=${searchQuery}`;
			const response = await fetch(url);
			const data = await response.json();
			const total = response.headers.get('X-WP-TotalPages');

			if (total) {
				setTotalPages(parseInt(total));
			}

			setJobs(data);
		} catch (error) {
			console.error('Error fetching jobs:', error);
			setJobs([]);
		}
		setLoading(false);
	};

	const handleSearch = (query) => {
		setSearchQuery(query);
		setCurrentPage(1);
	};

	const handlePageChange = (page) => {
		setCurrentPage(page);
		window.scrollTo({ top: 0, behavior: 'smooth' });
	};

	const openJobDetails = (job) => {
		setSelectedJob(job);
		setShowDetailsModal(true);
	};

	const openApplicationModal = (job) => {
		setSelectedJob(job);
		setShowApplicationModal(true);
		setShowDetailsModal(false);
	};

	const closeModals = () => {
		setShowDetailsModal(false);
		setShowApplicationModal(false);
	};

	return h('div', { className: 'ojb-job-board-container' },
		h(SearchBar, { onSearch: handleSearch }),
		loading
			? h('div', { style: { textAlign: 'center', padding: '40px' } },
				h('div', { className: 'ojb-loading' })
			)
			: h('div', null,
				h(JobGrid, {
					jobs,
					onViewDetails: openJobDetails,
					onApply: openApplicationModal
				}),
				totalPages > 1 && h(Pagination, {
					currentPage,
					totalPages,
					onPageChange: handlePageChange
				})
			),
		showDetailsModal && selectedJob && h(JobDetailsModal, {
			job: selectedJob,
			onClose: closeModals,
			onApply: () => openApplicationModal(selectedJob)
		}),
		showApplicationModal && selectedJob && h(ApplicationModal, {
			job: selectedJob,
			onClose: closeModals
		})
	);
};

// Initialize the app
document.addEventListener('DOMContentLoaded', () => {
	const rootElement = document.getElementById('ojb-job-board-root');
	if (rootElement) {
		const perPage = parseInt(rootElement.dataset.perPage) || 6;
		render(h(JobBoard, { perPage }), rootElement);
	}
});