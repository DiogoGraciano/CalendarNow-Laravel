interface AppLogoProps {
    showText?: boolean;
    className?: string;
}

export default function AppLogo({
    showText = true,
    className,
}: AppLogoProps) {
    const heightClass = showText ? 'h-10' : 'h-8';
    return (
        <div className={`flex items-center ${className ?? ''}`}>
            <img
                src="/logo.webp"
                alt="CalendarNow"
                className={`object-contain ${heightClass} w-auto`}
            />
        </div>
    );
}
