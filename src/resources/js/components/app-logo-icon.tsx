import { ImgHTMLAttributes } from 'react';

export default function AppLogoIcon({
    className,
    ...props
}: ImgHTMLAttributes<HTMLImageElement>) {
    return (
        <img
            src="/logo.webp"
            alt=""
            className={className ? `object-contain ${className}` : 'h-8 object-contain'}
            {...props}
        />
    );
}
