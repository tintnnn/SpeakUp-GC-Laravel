<x-layouts.complainant>
@php
    $videos = $videos ?? collect();
    if (! $videos instanceof \Illuminate\Support\Collection) {
        $videos = collect($videos);
    }
    $loadingVideos = (bool) ($loadingVideos ?? false);

    $topics = [
        [
            'icon' => 'heart',
            'title' => 'Self-Care Essentials',
            'description' => 'Simple yet powerful self-care practices for mental well-being',
            'benefits' => ['Reduces stress and anxiety', 'Improves mood and energy levels', 'Enhances self-esteem'],
            'introduction' => "Self-care is not selfish—it's essential for maintaining mental health and overall well-being.",
            'sections' => [
                ['title' => 'Physical Self-Care', 'content' => 'Regular exercise, balanced nutrition, and adequate sleep form the foundation of mental wellness. Even 15 minutes of daily movement can significantly boost mood and reduce anxiety.'],
                ['title' => 'Emotional Self-Care', 'content' => 'Practice identifying and expressing your feelings. Journaling, talking with trusted friends, or creative expression can help process emotions healthily.'],
                ['title' => 'Practical Tips', 'content' => "Start with small, manageable self-care activities. Create a weekly self-care plan that includes activities you genuinely enjoy, not just what you think you 'should' do."],
            ],
            'takeaways' => [
                'Self-care is personalized - what works for others may not work for you',
                'Consistency matters more than intensity',
                'Self-care includes setting healthy boundaries',
            ],
        ],
        [
            'icon' => 'brain',
            'title' => 'Understanding Anxiety',
            'description' => 'Learn about anxiety, its symptoms, and effective coping strategies',
            'benefits' => ['Identify anxiety triggers', 'Practical coping mechanisms', 'When to seek professional help'],
            'introduction' => "Anxiety is a normal human emotion that becomes problematic when it's persistent, excessive, and interferes with daily life.",
            'sections' => [
                ['title' => 'Types of Anxiety', 'content' => 'Generalized Anxiety Disorder (GAD), Panic Disorder, Social Anxiety, and Specific Phobias are common forms. Each has unique characteristics but shares the core feature of excessive fear or worry.'],
                ['title' => 'The Anxiety Cycle', 'content' => 'Anxiety often follows a pattern: trigger → anxious thoughts → physical symptoms → avoidance → temporary relief → reinforced anxiety. Breaking this cycle is key to management.'],
                ['title' => 'Evidence-Based Treatments', 'content' => 'Cognitive Behavioral Therapy (CBT), Exposure Therapy, and Mindfulness-Based Stress Reduction have strong research support. Medication may also be helpful for some individuals.'],
            ],
            'takeaways' => [
                'Anxiety is treatable with the right approaches',
                'Avoidance typically makes anxiety worse long-term',
                'Physical symptoms are real and part of the anxiety response',
            ],
        ],
        [
            'icon' => 'moon',
            'title' => 'Sleep & Mental Health',
            'description' => 'The crucial connection between quality sleep and emotional well-being',
            'benefits' => ['Sleep hygiene tips', 'Relaxation techniques', 'Improving sleep quality'],
            'introduction' => 'Sleep and mental health have a bidirectional relationship—poor sleep affects mental health, and mental health issues often disrupt sleep.',
            'sections' => [
                ['title' => 'The Sleep-Mood Connection', 'content' => 'During deep sleep, your brain processes emotional information and consolidates memories. Sleep deprivation negatively affects the amygdala (emotional center) and prefrontal cortex (rational thinking).'],
                ['title' => 'Sleep Hygiene Fundamentals', 'content' => 'Maintain consistent sleep/wake times, even on weekends. Keep your bedroom cool, dark, and quiet. Avoid screens 1-2 hours before bed as blue light suppresses melatonin production.'],
                ['title' => "When You Can't Sleep", 'content' => "If you haven't fallen asleep within 20 minutes, get up and do something relaxing in dim light. Return to bed only when you feel sleepy. This helps reassociate your bed with sleep rather than frustration."],
            ],
            'takeaways' => [
                'Most adults need 7-9 hours of quality sleep nightly',
                "Consistent bedtime routines signal your brain that it's time to sleep",
                'Caffeine has a 6-8 hour half-life - avoid it in the afternoon',
            ],
        ],
        [
            'icon' => 'activity',
            'title' => 'Mindfulness & Meditation',
            'description' => 'Techniques to stay present and reduce stress in daily life',
            'benefits' => ['Reduced stress levels', 'Improved focus', 'Better emotional regulation'],
            'introduction' => 'Mindfulness involves paying attention to the present moment without judgment, while meditation is the formal practice that develops this skill.',
            'sections' => [
                ['title' => 'Beginning Meditation', 'content' => 'Start with just 5 minutes daily. Focus on your breath, and when your mind wanders (which it will), gently return attention to breathing without self-criticism.'],
                ['title' => 'Informal Mindfulness Practices', 'content' => 'Bring mindful awareness to daily activities like eating, walking, or washing dishes. Notice sensations, smells, and textures without rushing to the next moment.'],
                ['title' => 'Common Challenges', 'content' => "Many beginners think they're 'bad at meditation' because their mind wanders. This is normal—the practice is in noticing the wandering and returning, not in having a blank mind."],
            ],
            'takeaways' => [
                'Mindfulness is a skill that develops with consistent practice',
                'Even short, regular practices create noticeable benefits',
                "There's no 'right way' to meditate - find what works for you",
            ],
        ],
        [
            'icon' => 'heart-pulse',
            'title' => 'Building Resilience',
            'description' => "Develop emotional strength to navigate life's challenges",
            'benefits' => ['Coping with setbacks', 'Developing a growth mindset', 'Building emotional intelligence'],
            'introduction' => "Resilience isn't about avoiding difficulties but about adapting well to adversity, trauma, tragedy, threats, or significant sources of stress.",
            'sections' => [
                ['title' => 'The Components of Resilience', 'content' => 'Resilience involves emotional regulation, impulse control, optimism, causal analysis (understanding causes of problems), empathy, self-efficacy, and reaching out.'],
                ['title' => 'Developing a Growth Mindset', 'content' => 'View challenges as opportunities for learning rather than insurmountable obstacles. Reframe failures as feedback and recognize that abilities can be developed through dedication and hard work.'],
                ['title' => 'Building Your Resilience Toolkit', 'content' => 'Maintain supportive relationships, take care of your physical health, practice mindfulness, set realistic goals, and look for opportunities for self-discovery during difficult times.'],
            ],
            'takeaways' => [
                'Resilience can be learned and strengthened at any age',
                'Social support is one of the strongest predictors of resilience',
                'Self-compassion is more helpful than self-criticism during hard times',
            ],
        ],
        [
            'icon' => 'users',
            'title' => 'Healthy Relationships',
            'description' => 'Nurturing positive connections and setting boundaries',
            'benefits' => ['Communication skills', 'Recognizing toxic relationships', 'Building support networks'],
            'introduction' => 'Healthy relationships contribute significantly to mental health, while unhealthy ones can be major sources of stress and emotional pain.',
            'sections' => [
                ['title' => 'Communication Foundations', 'content' => "Practice active listening—focus on understanding rather than preparing your response. Use 'I' statements to express feelings without blaming ('I feel worried when...' vs 'You make me worry when...')."],
                ['title' => 'Setting Healthy Boundaries', 'content' => "Boundaries define what is and isn't acceptable in how others treat you. They're not walls but gates you control. Clear boundaries actually enable closer, more authentic connections."],
                ['title' => 'Recognizing Relationship Red Flags', 'content' => 'Consistent disrespect, manipulation, control, dishonesty, or feeling drained after interactions may indicate an unhealthy dynamic. Trust your instincts about how relationships make you feel.'],
            ],
            'takeaways' => [
                'Healthy relationships involve mutual respect, trust, and support',
                "It's okay to distance yourself from consistently draining relationships",
                'Quality matters more than quantity in social connections',
            ],
        ],
    ];
@endphp
<div
    class="container mx-auto px-3 sm:px-4 py-4 sm:py-8 min-h-screen"
    x-data="{ tab: 'topics', selected: null, playing: null, minimized: false }"
>
    <template x-if="playing">
        <div class="fixed z-50 bottom-4 right-4 w-[min(24rem,calc(100vw-1rem))] bg-background rounded-xl shadow-2xl border border-border overflow-hidden flex flex-col">
            <div class="flex justify-between items-center p-3 bg-gradient-to-r from-green-500/10 to-blue-500/10 border-b border-border/30">
                <div class="flex items-center gap-2 flex-1 min-w-0">
                    <x-lucide-grip-vertical class="h-4 w-4 text-muted-foreground flex-shrink-0" />
                    <h3 class="text-xs font-semibold truncate" x-text="playing.title"></h3>
                </div>
                <div class="flex gap-1 flex-shrink-0">
                    <button type="button" @click="minimized = !minimized" class="h-6 w-6 inline-flex items-center justify-center rounded-md hover:bg-blue-50/50" :title="minimized ? 'Expand' : 'Minimize'">
                        <span x-show="minimized"><x-lucide-maximize-2 class="h-3 w-3" /></span>
                        <span x-show="!minimized"><x-lucide-minimize-2 class="h-3 w-3" /></span>
                    </button>
                    <button type="button" @click="playing = null; minimized = false" class="h-6 w-6 inline-flex items-center justify-center rounded-md hover:bg-destructive/20 hover:text-destructive" title="Close">×</button>
                </div>
            </div>
            <div x-show="!minimized">
                <div class="p-2 bg-black">
                    <div class="aspect-video w-full rounded">
                        <iframe class="rounded w-full h-full" width="100%" height="100%" :src="'https://www.youtube.com/embed/' + playing.videoId + '?autoplay=1'" :title="playing.title" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                    </div>
                </div>
                <div class="p-2 bg-muted/30 border-t border-border/30 text-xs">
                    <p class="font-medium text-foreground line-clamp-1" x-text="playing.title"></p>
                    <p class="text-muted-foreground text-xs">by <span x-text="playing.host"></span></p>
                    <div class="flex items-center gap-2 mt-1 flex-wrap">
                        <span class="bg-primary/20 text-primary px-1.5 py-0.5 rounded text-xs" x-text="playing.category"></span>
                        <span class="text-muted-foreground text-xs" x-text="playing.duration"></span>
                    </div>
                </div>
            </div>
        </div>
    </template>

    <div x-show="selected !== null" x-cloak>
        <button type="button" @click="selected = null" class="mb-4 sm:mb-6 inline-flex items-center px-3 py-2 text-sm sm:text-base font-medium rounded-md hover:bg-accent">
            <x-lucide-arrow-left class="mr-2 h-3 w-3 sm:h-4 sm:w-4" />
            Back to Topics
        </button>
        @foreach ($topics as $index => $topic)
            <div x-show="selected === {{ $index }}" @if ($index !== 0) x-cloak @endif class="rounded-lg border bg-card text-card-foreground shadow-sm max-w-6xl mx-auto">
                <div class="flex flex-col space-y-1.5 px-4 sm:px-6 py-4 sm:py-6">
                    <div class="flex items-center gap-2 sm:gap-3 mb-3 sm:mb-4">
                        <div class="p-1.5 sm:p-2 bg-primary/10 rounded-lg flex-shrink-0">
                            <x-dynamic-component :component="'lucide-'.$topic['icon']" class="h-4 w-4 sm:h-6 sm:w-6 text-primary" />
                        </div>
                        <h2 class="text-lg sm:text-2xl lg:text-3xl font-semibold leading-none tracking-tight break-words">{{ $topic['title'] }}</h2>
                    </div>
                    <p class="text-sm sm:text-lg text-muted-foreground break-words">{{ $topic['description'] }}</p>
                </div>
                <div class="space-y-6 sm:space-y-8 px-4 sm:px-6 pb-6 sm:pb-8">
                    <div class="p-4 sm:p-6 bg-muted/30 rounded-lg text-sm sm:text-base">
                        <h3 class="text-base sm:text-lg font-semibold mb-2 sm:mb-3">Introduction</h3>
                        <p class="leading-relaxed">{{ $topic['introduction'] }}</p>
                    </div>
                    @foreach ($topic['sections'] as $section)
                        <div class="space-y-3 sm:space-y-4">
                            <h3 class="text-base sm:text-xl font-semibold text-primary break-words">{{ $section['title'] }}</h3>
                            <p class="text-foreground leading-relaxed text-sm sm:text-base">{{ $section['content'] }}</p>
                        </div>
                    @endforeach
                    <div class="p-4 sm:p-6 bg-primary/5 rounded-lg border border-primary/20 text-sm sm:text-base">
                        <h3 class="text-base sm:text-lg font-semibold mb-3 sm:mb-4 flex items-center gap-2">
                            <x-lucide-circle-check class="h-4 w-4 sm:h-5 sm:w-5 text-primary flex-shrink-0" />
                            Key Takeaways
                        </h3>
                        <ul class="space-y-2 sm:space-y-3">
                            @foreach ($topic['takeaways'] as $takeaway)
                                <li class="flex items-start gap-2 sm:gap-3 break-words">
                                    <div class="h-1.5 w-1.5 sm:h-2 sm:w-2 bg-primary rounded-full mt-2 flex-shrink-0"></div>
                                    <span>{{ $takeaway }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                    <div class="p-4 sm:p-6 bg-muted/50 rounded-lg text-center text-sm sm:text-base">
                        <h4 class="font-semibold mb-2">Want to Learn More?</h4>
                        <p class="text-muted-foreground mb-3 sm:mb-4">Consider speaking with a mental health professional for personalized guidance.</p>
                        <button type="button" class="inline-flex items-center justify-center h-9 rounded-md bg-primary text-primary-foreground px-3 text-xs sm:text-sm font-medium">
                            <x-lucide-circle-check class="mr-2 h-3 w-3 sm:h-4 sm:w-4" />
                            Find Professional Resources
                        </button>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div x-show="selected === null">
        <div class="text-center mb-6 sm:mb-8 lg:mb-12">
            <h1 class="text-lg sm:text-2xl md:text-3xl lg:text-4xl xl:text-5xl font-bold text-foreground mb-2 sm:mb-3 lg:mb-4 break-words">
                Mental Health Resources
            </h1>
            <p class="text-xs sm:text-sm md:text-base lg:text-lg text-muted-foreground max-w-2xl mx-auto px-2 break-words">
                Valuable knowledge and practical tips for better mental well-being
            </p>
        </div>

        <div class="w-full">
            <div class="grid w-full grid-cols-3 max-w-md mb-6 sm:mb-8 mx-auto px-2 h-10 items-center justify-center rounded-md bg-muted p-1 text-muted-foreground">
                <button type="button" @click="tab = 'topics'" :class="tab === 'topics' ? 'bg-background text-foreground shadow-sm' : ''" class="text-xs sm:text-sm px-2 sm:px-4 py-2 rounded-sm font-medium">Topics</button>
                <button type="button" @click="tab = 'podcasts'" :class="tab === 'podcasts' ? 'bg-background text-foreground shadow-sm' : ''" class="text-xs sm:text-sm px-2 sm:px-4 py-2 rounded-sm font-medium">Podcasts &amp; Videos</button>
                <button type="button" @click="tab = 'tips'" :class="tab === 'tips' ? 'bg-background text-foreground shadow-sm' : ''" class="text-xs sm:text-sm px-2 sm:px-4 py-2 rounded-sm font-medium">Quick Tips</button>
            </div>

            <div x-show="tab === 'topics'">
                <div class="grid gap-4 sm:gap-6 grid-cols-1 min-[480px]:grid-cols-2 lg:grid-cols-3">
                    @foreach ($topics as $index => $topic)
                        <div class="h-full flex flex-col hover:shadow-md transition-shadow duration-200 min-w-0 break-words rounded-lg border bg-card text-card-foreground shadow-sm">
                            <div class="flex flex-col space-y-1.5 px-3 sm:px-6 py-3 sm:py-4">
                                <div class="flex items-center gap-2 sm:gap-3 mb-2 sm:mb-3">
                                    <div class="p-1.5 sm:p-2 bg-primary/10 rounded-lg flex-shrink-0">
                                        <x-dynamic-component :component="'lucide-'.$topic['icon']" class="h-4 w-4 sm:h-5 sm:w-5 text-primary" />
                                    </div>
                                    <h3 class="text-base sm:text-xl font-semibold leading-none tracking-tight break-words">{{ $topic['title'] }}</h3>
                                </div>
                                <p class="text-xs sm:text-sm text-muted-foreground break-words">{{ $topic['description'] }}</p>
                            </div>
                            <div class="flex-grow px-3 sm:px-6 py-2 sm:py-4">
                                <ul class="space-y-1.5 sm:space-y-2 text-xs sm:text-sm">
                                    @foreach ($topic['benefits'] as $benefit)
                                        <li class="flex items-start gap-1.5 sm:gap-2 break-words">
                                            <x-lucide-circle-check class="h-3 w-3 sm:h-4 sm:w-4 text-primary mt-0.5 flex-shrink-0" />
                                            <span>{{ $benefit }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                            <div class="flex items-center px-3 sm:px-6 py-3 sm:py-4">
                                <button type="button" @click="selected = {{ $index }}" class="w-full inline-flex items-center justify-center h-9 rounded-md border border-input bg-background text-xs sm:text-sm font-medium hover:bg-accent">
                                    Learn More
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div x-show="tab === 'podcasts'" x-cloak>
                @if ($loadingVideos)
                    <div class="flex items-center justify-center py-12">
                        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary"></div>
                    </div>
                @else
                    @forelse ($videos as $video)
                        @php
                            $videoTitle = (string) (data_get($video, 'title') ?? '');
                            $host = (string) (data_get($video, 'host') ?? '');
                            $description = (string) (data_get($video, 'description') ?? '');
                            $duration = (string) (data_get($video, 'duration') ?? '');
                            $category = (string) (data_get($video, 'category') ?? '');
                            $videoId = (string) (data_get($video, 'video_id') ?? data_get($video, 'videoId') ?? '');
                        @endphp
                        @if ($loop->first)
                            <div class="grid gap-4 sm:gap-6 grid-cols-1 min-[480px]:grid-cols-2 lg:grid-cols-3">
                        @endif
                        <div class="h-full flex flex-col hover:shadow-md transition-shadow duration-200 rounded-lg border bg-card text-card-foreground shadow-sm">
                            <div class="flex flex-col space-y-1.5 px-4 sm:px-6 py-4 sm:py-6">
                                <div class="flex items-start justify-between mb-3">
                                    <div class="p-2 bg-primary/10 rounded-lg">
                                        <x-lucide-headphones class="h-5 w-5 sm:h-6 sm:w-6 text-primary" />
                                    </div>
                                    <span class="text-xs bg-muted px-2 py-1 rounded-full text-muted-foreground">{{ $duration }}</span>
                                </div>
                                <h3 class="text-lg sm:text-xl font-semibold leading-none tracking-tight mb-2 break-words">{{ $videoTitle }}</h3>
                                <p class="text-sm text-muted-foreground break-words">Hosted by {{ $host }}</p>
                            </div>
                            <div class="flex-grow px-4 sm:px-6 py-2 sm:py-4">
                                <div class="space-y-3">
                                    <div>
                                        <h4 class="font-medium text-sm text-muted-foreground mb-1">Description</h4>
                                        <p class="text-sm text-foreground break-words">{{ $description }}</p>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs bg-primary/10 text-primary px-2 py-1 rounded-full">{{ $category }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center px-4 sm:px-6 py-4 sm:py-6">
                                <button
                                    type="button"
                                    class="w-full inline-flex items-center justify-center h-10 rounded-md bg-primary text-primary-foreground text-sm font-medium"
                                    data-title="{{ $videoTitle }}"
                                    data-host="{{ $host }}"
                                    data-video-id="{{ $videoId }}"
                                    data-category="{{ $category }}"
                                    data-duration="{{ $duration }}"
                                    @click="playing = { title: $el.dataset.title, host: $el.dataset.host, videoId: $el.dataset.videoId, category: $el.dataset.category, duration: $el.dataset.duration }; minimized = false"
                                >
                                    <x-lucide-play class="mr-2 h-4 w-4" />
                                    Watch Video
                                </button>
                            </div>
                        </div>
                        @if ($loop->last)
                            </div>
                            <div class="mt-8 p-6 bg-muted/30 rounded-lg text-center">
                                <h3 class="text-lg font-semibold mb-2">Looking for More Content?</h3>
                                <p class="text-muted-foreground">We're constantly updating our video library with helpful mental health resources.</p>
                            </div>
                        @endif
                    @empty
                        <div class="text-center py-12">
                            <x-lucide-headphones class="h-16 w-16 mx-auto text-gray-400 mb-4" />
                            <h3 class="text-lg font-semibold mb-2">No videos available yet</h3>
                            <p class="text-gray-600">Check back soon for mental health content</p>
                        </div>
                    @endforelse
                @endif
            </div>

            <div x-show="tab === 'tips'" x-cloak>
                <div class="grid gap-4 sm:gap-6 grid-cols-1 min-[480px]:grid-cols-2 lg:grid-cols-3">
                    <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
                        <div class="flex flex-col space-y-1.5 px-4 sm:px-6 py-4 sm:py-6">
                            <h3 class="text-lg sm:text-xl font-semibold leading-none tracking-tight">Daily Mental Health Tips</h3>
                        </div>
                        <div class="space-y-3 sm:space-y-4 px-4 sm:px-6 pb-4 sm:pb-6">
                            <div class="p-3 sm:p-4 bg-muted/50 rounded-lg text-sm sm:text-base">
                                <h4 class="font-medium mb-1.5 sm:mb-2 text-sm sm:text-base">Morning Routine</h4>
                                <p class="text-muted-foreground text-xs sm:text-sm">Start your day with 5 minutes of deep breathing or meditation to set a positive tone.</p>
                            </div>
                            <div class="p-3 sm:p-4 bg-muted/50 rounded-lg text-sm sm:text-base">
                                <h4 class="font-medium mb-1.5 sm:mb-2 text-sm sm:text-base">Digital Detox</h4>
                                <p class="text-muted-foreground text-xs sm:text-sm">Take regular screen breaks - try the 20-20-20 rule: every 20 minutes, look at something 20 feet away for 20 seconds.</p>
                            </div>
                            <div class="p-3 sm:p-4 bg-muted/50 rounded-lg text-sm sm:text-base">
                                <h4 class="font-medium mb-1.5 sm:mb-2 text-sm sm:text-base">Gratitude Practice</h4>
                                <p class="text-muted-foreground text-xs sm:text-sm">End your day by writing down three things you're grateful for to promote positive thinking.</p>
                            </div>
                        </div>
                    </div>
                    <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
                        <div class="flex flex-col space-y-1.5 px-4 sm:px-6 py-4 sm:py-6">
                            <h3 class="text-lg sm:text-xl font-semibold leading-none tracking-tight">Quick Stress Relievers</h3>
                        </div>
                        <div class="space-y-3 sm:space-y-4 px-4 sm:px-6 pb-4 sm:pb-6">
                            <div class="p-3 sm:p-4 bg-muted/50 rounded-lg text-sm sm:text-base">
                                <h4 class="font-medium mb-1.5 sm:mb-2 text-sm sm:text-base">5-4-3-2-1 Technique</h4>
                                <p class="text-muted-foreground text-xs sm:text-sm">Name 5 things you can see, 4 you can touch, 3 you can hear, 2 you can smell, and 1 you can taste.</p>
                            </div>
                            <div class="p-3 sm:p-4 bg-muted/50 rounded-lg text-sm sm:text-base">
                                <h4 class="font-medium mb-1.5 sm:mb-2 text-sm sm:text-base">Box Breathing</h4>
                                <p class="text-muted-foreground text-xs sm:text-sm">Inhale for 4 seconds, hold for 4, exhale for 4, hold for 4. Repeat 3-4 times.</p>
                            </div>
                            <div class="p-3 sm:p-4 bg-muted/50 rounded-lg text-sm sm:text-base">
                                <h4 class="font-medium mb-1.5 sm:mb-2 text-sm sm:text-base">Progressive Muscle Relaxation</h4>
                                <p class="text-muted-foreground text-xs sm:text-sm">Tense each muscle group for 5 seconds, then relax for 30 seconds, working from toes to head.</p>
                            </div>
                        </div>
                    </div>
                    <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
                        <div class="flex flex-col space-y-1.5 px-4 sm:px-6 py-4 sm:py-6">
                            <h3 class="text-lg sm:text-xl font-semibold leading-none tracking-tight">Life Tips</h3>
                        </div>
                        <div class="space-y-3 sm:space-y-4 px-4 sm:px-6 pb-4 sm:pb-6">
                            <div class="p-3 sm:p-4 bg-muted/50 rounded-lg text-sm sm:text-base">
                                <h4 class="font-medium mb-1.5 sm:mb-2 text-sm sm:text-base">Time Management</h4>
                                <p class="text-muted-foreground text-xs sm:text-sm">Prioritize tasks using the Eisenhower Matrix - separate urgent from important to stay focused.</p>
                            </div>
                            <div class="p-3 sm:p-4 bg-muted/50 rounded-lg text-sm sm:text-base">
                                <h4 class="font-medium mb-1.5 sm:mb-2 text-sm sm:text-base">Build Healthy Habits</h4>
                                <p class="text-muted-foreground text-xs sm:text-sm">Start small with one habit change at a time. Consistency beats perfection in building lasting routines.</p>
                            </div>
                            <div class="p-3 sm:p-4 bg-muted/50 rounded-lg text-sm sm:text-base">
                                <h4 class="font-medium mb-1.5 sm:mb-2 text-sm sm:text-base">Set Boundaries</h4>
                                <p class="text-muted-foreground text-xs sm:text-sm">Learn to say no. Setting healthy boundaries protects your energy and improves relationships.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-8 sm:mt-12 bg-muted/50 rounded-lg p-4 sm:p-6 lg:p-8 text-center">
            <h3 class="text-lg sm:text-xl font-semibold mb-2 break-words">Need More Support?</h3>
            <p class="text-muted-foreground mb-3 sm:mb-4 text-sm sm:text-base break-words">
                Remember, it's okay to ask for help. Reach out to a trusted friend, family member, or professional.
            </p>
        </div>
    </div>
</div>
</x-layouts.complainant>
